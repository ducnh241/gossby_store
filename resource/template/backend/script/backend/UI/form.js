(function($) {
    function OSC_Backend_Form(frm, options) {
        this.enable = function() {
            
        };
        
        this.disable = function() {
            
        };
        
        this.submit = function(skip_error){
            if(this.validate() || skip_error){
                this.form[0].submit();
            }
        };
        
        this.ready = function() {
            this.tab_ready_counter ++;
            
            if(this.tab_ready_counter == this.total_tab) {
                this.enable();
                this.switchTab(this.default_tab);
            }
        };
        
        this.validate = function(){
            var error = 0;
            var error_tab = [];

            for(var x in this.tabs) {
                for(var y in this.tabs[x].elements) {
                    if(this.tabs[x].elements[y].is_label) {
                        continue;
                    }

                    if(typeof this.tabs[x].elements[y].getter == 'function') {
                        this.tabs[x].elements[y].getter({inst : this, tab : this.tabs[x], element : this.tabs[x].elements[y]});
                    }

                    if(this.tabs[x].elements[y].validator) {
                        if(! $.validate(this.tabs[x].elements[y].validator) && this.tabs[x].elements[y].require) {
                            error ++;
                        }
                    }
                }
            }

            return error < 1;
        };
        
        this.getCurrentTab = function() {
            return this.frm.find('.mrk-backend-frm-tabs > li.mrk-active');
        };
        
        this.getCurrentTabIndex = function() {
            return this.getCurrentTab().attr('data-tab');
        };
        
        this.getCurrentTabForm = function() {
            return this.getTabForm(this.getCurrentTabIndex());
        };
        
        this.getTabForm = function(tab_idx) {
            return this.frm.find('.mrk-backend-frm-elements[data-tab="' + tab_idx + '"]');
        };
        
        this.switchTab = function(TAB) {
            var TAB_IDX = TAB.attr('data-tab');
            var CUR_TAB = this.getCurrentTab();
            var CUR_TAB_IDX = this.getCurrentTabIndex();
            
            if(CUR_TAB_IDX) {
                if(CUR_TAB_IDX == TAB_IDX) {
                    return;
                }

                CUR_TAB.removeClass('active').removeClass('mrk-active');
                this.getTabForm(CUR_TAB_IDX).slideUp('fast');
            }
            
            var self = this;
                
            TAB.addClass('active').addClass('mrk-active');
            this.getTabForm(TAB_IDX).slideDown('fast', function() {
                
            });
        };
        
        this.addParam = function(key, val){
            var obj = $('#' + this.index + '__rParam__' + key);

            if(! obj[0]){
                obj = $('<input />').attr({type : 'hidden', name : key, id : inst.index + '__rParam__' + key}).appendTo('#' + this.index + '__elements');
            }

            obj.val(val);

            return this;
        };
        
        this.removeParam = function(key) {               
            $('#' + this.index + '__rParam__' + key).remove();

            return this;
        };
        
        this._initDepends = function(elm) {            
            var depends = elm.attr('data-depends');
            eval('depends = ' + depends);
            
            var self = this;
            
            for(var i in depends) {
                $('#' + i).change(function() { self._checkDepends(elm, depends); });
            }
            
            self._checkDepends(elm, depends);
        };
        
        this._checkDepends = function(elm, depends) {
            var flag = true;
                
            $.each(depends, function(elm_id, depend_value) {
                var elm_value = $('#' + elm_id).getVal();

                if(typeof elm_value != 'object') {                            
                    elm_value = [elm_value];
                }

                if(typeof depend_value != 'object') {
                    depend_value = [depend_value];
                }

                $.each(depend_value, function(k, v) {
                    var checked = false;

                    $.each(elm_value, function(x, y) {
                        if(v == y) {
                            checked = true;
                        }

                        return ! checked;
                    });

                    flag = checked;                            

                    return flag;
                });
                
                return flag;
            });

            if(flag) {
                elm.show().removeClass('deactive');
            } else {
                elm.hide().addClass('deactive');
            }
                        
            this.frm.find('tr.field-row.last').removeClass('last');                         
            this.frm.find('tr.field-row:not(.deactive):last').addClass('last'); 
        };
        
        if(typeof options != 'object') {
            options = {};
        }
            
        var self = this;
        
        this.tabs = null;
                            
        $.extend(this, options);
            
        this.frm = $(frm);
            
        this.tabs = this.frm.find('.mrk-backend-frm-tabs > li');
        this.tabs.click(function() { self.switchTab($(this)); });
        
        this.frm.find('tr.field-row[data-depends]').each(function() {
            self._initDepends($(this));
        });         
//            
//            
//            
//            this.tab_ready_counter = 0;
//            this.total_tab = tab_collection.length;
//            
//            this.form = $('#' + this.index + '__form');
//
//            this.form[0].onsubmit = function(){
//                return self.validate();
//            };
//                    
//            tab_collection.click(function() {
//                self.switchTab($(this).attr('rel'));
//            }).each(function() {
//                var tab_index = $(this).attr('rel');
//                
//                self.tabs[tab_index].tab = $(this);
//                self.tabs[tab_index].content = $('#' + self.index + '__' + tab_index + '__elements');
//                    
//                var func_keys = ['maker', 'ready', 'setter', 'getter', 'validator'];
//                
//                for(var i in self.tabs[tab_index].elements){
//                    
//                    if(self.tabs[tab_index].elements[i].is_label){
//                        continue;
//                    }
//                    
//                    $(func_keys).each(function(idx, func_key) {
//                        if(typeof self.tabs[tab_index].elements[i][func_key] == 'string') {
//                            if(self.tabs[tab_index].elements[i][func_key].trim().toLowerCase().lastIndexOf('function') == 0) {
//                                eval('self.tabs[tab_index].elements[i][func_key] = ' + self.tabs[tab_index].elements[i][func_key]);
//                            }
//                        }
//                    });
//
//                    self.tabs[tab_index].elements[i].id = self.index + '__' + tab_index + '__elements__' + self.tabs[tab_index].elements[i].index;
//
//                    if(typeof self.tabs[tab_index].elements[i].maker == 'function'){
//                        self.tabs[tab_index].elements[i].maker({inst : self, tab : self.tabs[tab_index], element : self.tabs[tab_index].elements[i]});
//                    }
//                    
//                    if(typeof self.tabs[tab_index].elements[i].validator != 'function'){
//                        switch(self.tabs[tab_index].elements[i].validator){
//                            case 'number':
//                                self.tabs[tab_index].elements[i].validator = {};
//                                self.tabs[tab_index].elements[i].validator[self.tabs[tab_index].elements[i].id] = 'number';
//                                break;
//                            default:
//                                self.tabs[tab_index].elements[i].validator = {};
//                                self.tabs[tab_index].elements[i].validator[self.tabs[tab_index].elements[i].id] = 'string';
//                        }
//                    }
//
//                    if(typeof self.tabs[tab_index].elements[i].setter == 'function'){
//                        self.tabs[tab_index].elements[i].setter({inst : self, tab : self.tabs[tab_index], element : self.tabs[tab_index].elements[i]});
//                    }
//
//                    self.tabs[tab_index].elements[i].frm = $('#' + self.tabs[tab_index].elements[i].id);
//                    
//                    if(typeof self.tabs[tab_index].elements[i].ready == 'function'){
//                        if(! self.tabs[tab_index].elements[i].ready({inst : self, tab : self.tabs[tab_index], element : self.tabs[tab_index].elements[i]})){
//                            setTimeout(function(){ self.ready(); }, 100);
//                        } else {
//                            self.ready();
//                        }
//                    } else {
//                        self.ready();
//                    }
//                }
//            });
//            
//            this.disable();
    }
    
    $.fn.osc_backend_form = function() {
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
            var instance = $(this).data('osc-backend-form');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-backend-form', new OSC_Backend_Form(this, opts));
                }
            }
        });
    }
})(jQuery);