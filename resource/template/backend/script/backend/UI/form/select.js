(function($) {
    $.OSC_UI_Form_Select_Menu_Holder = $('<div />').addClass('osc-ui-frm-select-menu-holder');
    $.OSC_UI_Form_Select_Menu_Collect_Additional_Height = null;
    
    $(window.document.body).append($.OSC_UI_Form_Select_Menu_Holder);
    
    function OSC_UI_Form_Select(target, options) {
        this.render = function() {
            this.toggler = $('<div />').addClass('toggler');
            this.selected_item = $('<div />').addClass('selected-item');
            this.selected_label = $('<div />').html(this.select[0].options[this.select[0].selectedIndex].label);
            
            this.selected_item.append(this.selected_label);
            this.container.append(this.toggler);
            this.container.append(this.selected_item);
            
            if(this.select.css('width')) {
                this.container.width(parseInt(this.select.css('width')));
            }
            
            var self = this;
            
            this.select.bind('change', function() {
                var label = this.options[this.selectedIndex].label;                
                self.selected_label.html(label).attr('title', label);
            });
            
            $(this.container).osc_toggleMenu({
                menu : $.OSC_UI_Form_Select_Menu_Holder,
                toggle_mode : 'br',
                divergent_y : 4,
                divergent_x : 1,
                open_hook : function(params) {
                    $.OSC_UI_Form_Select_Menu_Holder.html('');
                    
                    var close_toggle_menu = function() {
                        params.inst.hide();
                        $(window).unbind('scroll', close_toggle_menu);
                    }
                    
                    $(window).scroll(close_toggle_menu);
                    
                    var wrap = $('<div />');
                    var container = $('<ul />');
                    
                    $.OSC_UI_Form_Select_Menu_Holder.append(wrap);
                    wrap.append(container);
                    
                    for(var x = 0; x < self.select[0].options.length; x ++) {
                        var label = self.select[0].options[x].label;
                        
                        var item = $('<li />').append($('<div />').html(label)).attr('title', label);
                        
                        if(x == self.select[0].selectedIndex) {
                            item.addClass('selected');
                        }
                        
                        item.css('min-width', $(params.inst.toggler).width() + 'px');
                        
                        item.attr('rel', x).click(function() {
                            self.select[0].options[$(this).attr('rel')].selected = true;
                            self.select.trigger('change');
                            
                            if(! self._DIS_CLOSE_TOGGLE_MENU_FLAG) {
                                close_toggle_menu();
                            }
                            
                            self._DIS_CLOSE_TOGGLE_MENU_FLAG = false;
                        });
                        
                        container.append(item);
                    }
                    
                    var item_height = item.height();
                    
                    var additional_height = 0;
                    
                    if(typeof $.OSC_UI_Form_Select_Menu_Collect_Additional_Height == 'function') {
                        additional_height = $.OSC_UI_Form_Select_Menu_Collect_Additional_Height();
                    }

                    var max_item_display = Math.ceil(($(window).height()-(params.inst.toggler.offset().top + params.inst.toggler.height() + params.inst.divergent_y-$(window).scrollTop())-additional_height)/item_height);

                    if(max_item_display < 5) {                    
                        if(typeof $.OSC_UI_Form_Select_Menu_Collect_Additional_Height == 'function') {
                            additional_height = $.OSC_UI_Form_Select_Menu_Collect_Additional_Height(true);
                        }
                    
                        var _max_item_display = Math.ceil((params.inst.toggler.offset().top - ($(window).scrollTop() + additional_height) - params.inst.divergent_y)/item_height);
                        
                        if(_max_item_display > max_item_display) {
                            max_item_display = _max_item_display;
                        }
                    }

                    wrap.css('max-height', max_item_display * item_height + 'px').osc_scroller({transfer_scroll : false});
                    
                    $(document).keydown(self._func_hook.doc_key_down);
                },
                after_open_hook : function(params) {
                    params.inst.setPosition();
                },
                close_hook : function(params) {
                    $.OSC_UI_Form_Select_Menu_Holder.html('');
                    $(document).unbind('keydown', self._func_hook.doc_key_down);
                }
            });
        }
        
        this._keyDownHook = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var cur_item = $.OSC_UI_Form_Select_Menu_Holder.find('ul li.selected');
            
            if(e.keyCode == 38) {
                if(cur_item[0]) {
                    cur_item.removeClass('selected');
                    cur_item = cur_item.prev();
                }
                
                if(! cur_item[0]) {
                    cur_item = $.OSC_UI_Form_Select_Menu_Holder.find('ul li:last-child');
                }
            } else if(e.keyCode == 40) {
                if(cur_item[0]) {
                    cur_item.removeClass('selected');
                    cur_item = cur_item.next();
                }
                
                if(! cur_item[0]) {
                    cur_item = $.OSC_UI_Form_Select_Menu_Holder.find('ul li:first-child');
                }
            } else {
                return;
            }
            
            this._DIS_CLOSE_TOGGLE_MENU_FLAG = true;
            cur_item.addClass('selected').trigger('click');
            $.OSC_UI_Form_Select_Menu_Holder.find('> div').osc_scroller('scrollToObj', cur_item);            
        };
        
        this.renderMulti = function() {            
            var self = this;
            
            this.skip_change_event = false;
            
            this.container.addClass('multiple');
            
            var item_wrap = $('<div />');
            var item_container = $('<ul />');

            this.container.append(item_wrap);
            item_wrap.append(item_container);
            
            this.scroll_data = false;
            
            var restore_item = function(item) {
                if(item.attr('rel') == self.scroll_data.target_rel) {
                    return true;
                }
                
                item[item.attr('bakstate') == 1 ? 'addClass' : 'removeClass']('selected');
            };
            
            var process_item = function(item, skip_restore_sibling) {
                var action = self.scroll_data.action;
                
                var rel = parseInt(item.attr('rel'));
                
                if(rel == self.scroll_data.target_rel) {
                    item[action == 'select' ? 'addClass' : 'removeClass']('selected');
                } else {
                    if(action == 'select') {
                        item.addClass('selected');
                    } else if(item.attr('bakstate') == 1) {
                        item.removeClass('selected');
                    }
                }
                
                if(! skip_restore_sibling) {
                    if(rel < self.scroll_data.target_rel) {
                        item = item.prev();
                    } else if(rel > self.scroll_data.target_rel) {
                        item = item.next();
                    } else {
                        if(item.next()[0]) {
                            restore_item(item.next());
                        }

                        if(item.prev()[0]) {
                            restore_item(item.prev());                        
                        }

                        return;
                    }

                    if(item[0]) {
                        restore_item(item);
                    }
                }
            };
            
            var scroll_up = function() {
                clearTimeout(self.scroll_data.timer);
                
                if(self.scroll_data.scroll_item.prev()[0]) {
                    self.scroll_data.scroll_item = self.scroll_data.scroll_item.prev();                    
                    item_wrap.osc_scroller('scrollToObj', self.scroll_data.scroll_item);   
                    
                    var item = self.scroll_data.scroll_item;
                    
                    if(item[0]) {
                        process_item(item);
                    }
                    
                    self.scroll_data.timer = setTimeout(scroll_up, 50);
                }
            };
            
            var scroll_down = function() {
                clearTimeout(self.scroll_data.timer);                
                item_wrap.osc_scroller('scrollToObj', self.scroll_data.scroll_item);
                
                var item = self.scroll_data.scroll_item;
                
                for(var x = 0; x < self.scroll_data.item_per_scene; x ++) {
                    item = item.next();
                    
                    if(! item[0]) {
                        break;
                    }
                }

                if(item[0]) {
                    process_item(item);
                    
                    self.scroll_data.scroll_item = self.scroll_data.scroll_item.next();
                    self.scroll_data.timer = setTimeout(scroll_down, 50);
                }
            };
            
            var mouse_move_hook = function(e) {
                if(e.pageY < self.scroll_data.box_y) {
                    if(self.scroll_data.direction != 'up') {
                        self.scroll_data.direction = 'up';
                        scroll_up();
                    }
                } else if(e.pageY > self.scroll_data.box_y + self.scroll_data.box_h) {
                    if(self.scroll_data.direction != 'down') {
                        self.scroll_data.direction = 'down';
                        scroll_down();        
                    }
                } else {
                    clearTimeout(self.scroll_data.timer);
                    self.scroll_data.direction = null;
                    
                    var item = self.scroll_data.scroll_item;
                    
                    do {
                        if(parseInt(item.attr('rel')) > self.scroll_data.target_rel) {
                            if(item.offset().top > e.pageY) {
                                restore_item(item);
                            } else {
                                process_item(item, true);
                            }
                        } else if(parseInt(item.attr('rel')) < self.scroll_data.target_rel) {                            
                            if(item.offset().top + item.height() >= e.pageY) {
                                process_item(item, true);
                            } else {
                                restore_item(item);
                            }                            
                        }
                        
                        item = item.next();
                    } while(item[0] && item.offset().top <= self.scroll_data.box_y + self.scroll_data.box_h);
                }
            };
            
            var doc_mouse_up_hook = function() {
                clearTimeout(self.scroll_data.timer);
                
                self.skip_change_event = true;
                    
                self.scroll_data.items.each(function() {
                    var item = $(this);                        
                    self.select[0].options[item.attr('rel')].selected = item.hasClass('selected');
                });

                self.skip_change_event = false;

                $(document).unbind('mousemove', mouse_move_hook).unbind('mouseup', doc_mouse_up_hook);
                $(document.body).removeClass('osc-ui-frm-select-dis-sel');
                self.scroll_data = null;
                self.select.trigger('change');
            };
            
            for(var x = 0; x < this.select[0].options.length; x ++) {
                var label = this.select[0].options[x].label;

                var item = $('<li />').append($('<div />').html(label)).attr('title', label);

                item.attr('rel', x).mousedown(function(e) {
                    if(! $.browser.opera) {
                        if($.browser.msie) {
                            if(e.button == 2) {
                                return;                                
                            }
                        } else if(e.which == 3) {
                            return;
                        }
                    }   
                    
                    var item = $(this);
                    
                    var rel = parseInt(item.attr('rel'));
                    
                    var item_collection = $('li', self.container[0]);
                    
                    var scroll_action = null;
                                                            
                    if(e.ctrlKey) {
                        item.toggleClass('selected');                        
                        scroll_action = item.hasClass('selected') ? 'select' : 'unselect';
                        
                        item_collection.each(function() {
                            var item = $(this);     
                            item.attr('bakstate', item.hasClass('selected') ? 1 : 0);
                        });
                    } else {
                        item_collection.each(function() {
                            var item = $(this);                            
                            item[item.attr('rel') == rel ? 'addClass' : 'removeClass']('selected');
                            item.attr('bakstate', item.hasClass('selected') ? 1 : 0);
                        });
                        
                        scroll_action = 'select';
                    }
                    
                    self.scroll_data = {
                        target_rel : rel,
                        target_h : item.height(),
                        item_per_scene : Math.floor(item_wrap.height()/item.height()),
                        target : item,
                        action : scroll_action,
                        box_y : item_wrap.offset().top,
                        box_h : item_wrap.height(),
                        items : item_collection,
                        scroll_item : null,
                        direction : null,
                        timer : null
                    };
                    
                    self.scroll_data.scroll_item = item;
                    
                    while(self.scroll_data.scroll_item.offset().top > self.scroll_data.box_y && self.scroll_data.scroll_item.prev()[0]) {
                        self.scroll_data.scroll_item = self.scroll_data.scroll_item.prev();
                    }
                    
                    if(self.scroll_data.scroll_item.next()[0]) {
                        self.scroll_data.scroll_item = self.scroll_data.scroll_item.next();
                    }
                    
                    $(document).bind('mousemove', mouse_move_hook).mouseup(doc_mouse_up_hook);
                    $(document.body).addClass('osc-ui-frm-select-dis-sel');
                });

                item_container.append(item);
            }
            
            if(this.select.css('width')) {
                this.container.width(parseInt(this.select.css('width')));
            }
            
            var box_height = parseInt(this.select.css('height'));
            
            if(isNaN(box_height) || box_height < 1) {
                box_height = this.size * $('.osc-ui-frm-select-menu-item', this.container[0]).height();
            }
            
            var mark_selected_item = function() {
                if(self.skip_change_event) {
                    return true;
                }
                
                $('.osc-ui-frm-select-menu-item', self.container[0]).each(function() {
                    var item = $(this);
                    item[self.select[0].options[item.attr('rel')].selected ? 'addClass' : 'removeClass']('selected');
                });
            };
            
            this.select.bind('change', mark_selected_item);
            
            mark_selected_item();
            
            item_wrap.css('max-height', box_height + 'px').osc_scroller();
        };
        
        if(target.tagName == 'SELECT') {
            options.container = $('<div />').addClass('osc-select');
            $(target).after(options.container);
            $(options.container).append(target);
            options.select = $(target);
            var attr_keys = ['name','id','value','disabled','multiple','size'];
            
            for(var x = 0; x < attr_keys.length; x ++) {
                if(options.select.attr(attr_keys[x])) {
                    options[attr_keys[x]] = options.select.attr(attr_keys[x]);
                }
            }
        } else {
            options.container = $(target);
            options.select = $('<select />');
        }
        
        var self = this;
        
        this.container = null;
        this.multiple = null;
        this.select = null;
        this.type = 1;
        this.name = null;
        this.value = null;
        this.disabled = false;
        this.id = null;
        this._DIS_CLOSE_TOGGLE_MENU_FLAG = false;
                
        $.extend(this, options);
        
        this._func_hook = {};
        this._func_hook.doc_key_down = function(e) {
            self._keyDownHook(e);
        }
        
        
        this.size = parseInt(this.size);
        
        if(isNaN(this.size) || this.size < 4) {            
            this.size = this.multiple ? 4 : 1;
        }

        this.transition_class = this.type < 2 ? 'transition-linear-0-05' : this.type > 2 ? 'transition-linear-0-15' : 'transition-linear-0-10';

        if(this.multiple) {
            this.renderMulti();
        } else {
            this.render();
        }
    }
    
    $.fn.osc_UIFormSelect = function() {        
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
            var instance = $(this).data('osc-ui-frm-select');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-ui-frm-select', new OSC_UI_Form_Select(this, opts));
                }
            }
        });
    }
    
    $(window).ready(function() {
        $('.mrk-ui-frm-select').each(function() {
            var obj = $(this);
            
            var opt_buff = obj.attr('rel') ? obj.attr('rel').split(';') : [];

            var opts = {};
            
            for(var x = 0; x < opt_buff.length; x ++) {
                var opt = opt_buff[x].split(':');
                
                if(opt.length != 2) {
                    continue;
                }
                
                opt[0] = opt[0].toString().trim();
                opt[1] = opt[1].toString().trim();
                
                if(opt[0] && opt[1]) {
                    opts[opt[0]] = opt[1];
                }
            }
           
            obj.osc_UIFormSelect(opts);            
        });
    });
})(jQuery);