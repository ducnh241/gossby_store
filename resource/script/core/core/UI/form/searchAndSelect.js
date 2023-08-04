(function($) {
    $.OSC_UI_SEARCH_AND_SELECT_POPUP = $('<div />').addClass('search-and-select-popup').appendTo(document.body);
    
    function OSC_UI_Search_And_Select(elm, options) {
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
            
            if(this.tabindex) {
                this._search_input.attr('tabindex', this.tabindex);
            } else {
                this._search_input.removeAttr('tabindex');
            }
        };
        
        this._render = function(keywords) {
            if(! this._search_result) {
                this._loadSearchResult(keywords);
                return;
            }
            
            var self = this;
            
            this._active_item = null;
            
            $.OSC_UI_SEARCH_AND_SELECT_POPUP.html('');
            
            var ul = $('<ul />').addClass('result-list').appendTo($('<div />').addClass('result-list-wrap').appendTo($.OSC_UI_SEARCH_AND_SELECT_POPUP));
            
            var selected_idx = this._hidden_input.attr('data-idx');
            
            for(var i in this._search_result.items) {
                var item = this._search_result.items[i];                
                var li = $('<li />').appendTo(ul);
                
                if(selected_idx == i) {
                    li.addClass('selected');
                }
                
                if(! this.item_renderer) {
                    $('<div />').html(item.label).appendTo(li);
                } else {
                    li.append(this.item_renderer(item));
                }
                
                li.attr('data-idx', i);
                li.click(function(e){
                    self._selecting_flag = false;
                    
                    var item = self._search_result.items[$(this).attr('data-idx')];  
                    
                    self._hidden_input.val(item.value).attr('data-idx', $(this).attr('data-idx'));
                    
                    if(! self.selected_item_renderer) {
                        self._selected_label.html(item.label);
                    } else {
                        self._selected_label.append(self.selected_item_renderer(item));
                    }
                    
                    try {
                        self.selected_hook({e : e, inst : self, item : item});
                    } catch(e) {}
                    
                    self._container.osc_toggleMenu('hide', e);
                });
            }
            
            var pager = $.pager(this._search_result.current_page, this._search_result.total_page, null, null, 3);
            
            if(pager) {
                var pager_container = $('<div />').addClass('pager-bar').appendTo($.OSC_UI_SEARCH_AND_SELECT_POPUP);            
                pager_container.append(pager);
                pager.find('a').click(function(e){
                    e.preventDefault();
                    self._loadSearchResult(keywords, $(this).attr('data-page'));
                });
            }
            
            this._container.osc_toggleMenu('setPosition');
        };
        
        this._loadSearchResult = function(keywords, page) {
            var self = this;
                
            if(this._search_locked_flag) {
                clearTimeout(this._search_recall_flag);
                
                this._search_recall_flag = setTimeout(function(){ self._loadSearchResult(keywords); }, 500);
                
                return;
            }
            
            this._search_locked_flag = true;
            
            $.ajax({
                type: "POST",
                url: this.search_url,
                data: {keywords : keywords, page : page},
                success: function(response){	
                    eval("response=" + response + ';');
			
                    if(response.result == 'ERROR') {
                        alert(response.data.message);
                        self._search_locked_flag = false;
                        return false;
                    }
                    
                    self._search_result = response.data;
                    
                    self._render(keywords);
                        
                    self._search_locked_flag = false;
                }
            });
        };
        
        this._moveActiveItem = function(up_flag) {
            var item_list = $.OSC_UI_SEARCH_AND_SELECT_POPUP.find('.result-list');
            var item_list_wrap = item_list.parent();
            
            if(! this._active_item || ! this._active_item[0]) {
                if(up_flag) {
                    this._active_item = item_list.find('> li:last-child');
                } else {
                    this._active_item = item_list.find('> li:first-child');
                }
            } else {
                this._active_item.removeClass('active');            
            
                this._active_item = this._active_item[up_flag ? 'prev' : 'next']();

                if(! this._active_item[0]) {
                    if(up_flag) {
                        this._active_item = item_list.find('> li:last-child');
                    } else {
                        this._active_item = item_list.find('> li:first-child');
                    }
                }
            }
            
            this._active_item.addClass('active');
            
            var scroll_top = item_list_wrap.scrollTop();
            var list_height = item_list_wrap.height();
            
            if(this._active_item.position().top + this._active_item.height() > scroll_top + list_height) {
                item_list_wrap.scrollTop(this._active_item.position().top - list_height + this._active_item.height());
            } else if(this._active_item.position().top < scroll_top) {
                item_list_wrap.scrollTop(this._active_item.position().top);                
            }
            
            this._selecting_flag = true;
        };
        
        var self = this;
        
        if(typeof elm == 'string' || ! elm.jquery) {
            elm = $(elm);
        }
        
        var input = null;
        var container = null;
        
        if(elm[0].tagName == 'INPUT') {
            input = elm;
            container = $('<div />').insertBefore(elm);
            container.append(elm);
            
            if(input.width() > 0) {
                container.width(input.width());
            }
        } else {
            container = elm;
            container.html('');
            input = $('<input />').appendTo(container);
        }
        
        container.addClass('search-and-select');
        
        input.prop('type', 'hidden');
        
        this._container = container;
        this._hidden_input = input;
        this._search_input = $('<input />').prop('type', 'text').addClass('search').appendTo(this._container);
        this._selected_label = $('<div />').addClass('label').appendTo(this._container);
        this._search_result = null;
        this._search_locked_flag = false;
        this._search_recall_flag = null;
        this._selecting_flag = false;
        this._active_item = null;
        this._input_blur_hide_timer = null;
        
        this.search_url = null;
        this.item_renderer = null;
        this.selected_item_renderer = null;
        this.selected_hook = null;
        this.tabindex = null;
        
        this.config(options);
        
        this._search_input.keydown(function(e) {
            if(! self._selecting_flag && e.keyCode == 13) {                
                self._loadSearchResult(self._search_input.val());
                return false;
            }
            
            switch(e.keyCode) {
                case 40:
                    self._moveActiveItem(0);
                    break;
                case 38:
                    self._moveActiveItem(1);
                    break;
                case 13:           
                    if(self._active_item && self._active_item[0]) {
                        self._active_item.trigger('click');
                        self._search_input[0].blur();
                    }
                    break;
                default:
                    self._selecting_flag = false;
                    return true;
            }   
                    
            return false;
        });
        this._container.osc_toggleMenu({
            menu : $.OSC_UI_SEARCH_AND_SELECT_POPUP,
            divergent_x : 1,
            divergent_y : 1,
            open_hook : function(params){
                self._render();
                self._search_input[0].focus();
                clearTimeout(self._input_blur_hide_timer);
            },
            close_hook : function(){
                $.OSC_UI_SEARCH_AND_SELECT_POPUP.html('');
            }
        });
        
        this._search_input.focus(function(e){
            self._container.osc_toggleMenu('show', e);
        }).blur(function(e) {
            self._input_blur_hide_timer = setTimeout(function(){ self._container.osc_toggleMenu('hide', e); }, 500);
        });
    }
	
    $.fn.osc_UI_searchAndSelect = function() {
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
            var instance = $(this).data('osc-ui-search-and-select');
            
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-ui-search-and-select', new OSC_UI_Search_And_Select(this, opts));
                }
            }
        });
    };
})(jQuery);