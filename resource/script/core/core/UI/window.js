(function($) {    
    function OSC_UI_Window(options) {            
        this.render = function() {       
            var self = this;
        
            this.win = $('<div />').addClass('osc-win');
            $(document.body).append(this.win);
            this.win.swapZIndex();
            
            this.title_bar = $('<div />').addClass('head');
            this.win.append(this.title_bar);
            
            this.close_btn = $('<span />').addClass('close-btn').append($('<i />')).click(function(e){ self.destroy(e); });
            this.title_bar.append(this.close_btn);
            
            this.title_container = $('<div />');
            this.title_bar.append(this.title_container);
            if(this.title) {
                this.title_container.html(this.title);
            }            
            this.title_bar.osc_dragger({
                target : this.win,
                fire_hook_callback : function(params){ self.swapDepths(); }
            });
            
            this.scene = $('<div />').addClass('scene');
            this.win.append(this.scene);
            if(this.content) {
                this.scene.html(this.content);
            }
            
            this.dis_wrap = $('<div />').addClass('dis-wrap');
            this.win.append(this.dis_wrap);        
            this.dis_wrap.osc_dragger({
                target : this.win,
                fire_hook_callback : function(params){ self.swapDepths(); }
            });
            
            this.win.moveToCenter();
        };
        
        this.swapDepths = function() {            
            this.win.swapZIndex();
        };
        
        this.disable = function() {
            this.dis_wrap.show();
        };
        
        this.enable = function() {
            this.dis_wrap.hide();
        };
        
        this.destroy = function(e, force) {
            if(! force) {
                if(typeof this.destroy_hook === 'function') {
                    if(this.destroy_hook(e) === false) {
                        return false;
                    }
                }
            }
            
            var buff = [];
            var inst = null;
            
            for(var x = 0; x < $.osc_ui_win_collection.length; x ++) {                
                if(x !== this.index) {                    
                    inst = $.osc_ui_win_collection[x];
                    inst.index = buff.length;
                    buff.push(inst);
                }
            }
            
            $.osc_ui_win_collection = buff;

            this.win.remove();
        };
        
        if(typeof options !== 'object') {
            options = {};
        }
        
        options.index = $.osc_ui_win_collection.length;
        
        this.index = null;
        this.destroy_hook = null;
        this.title = null;
        this.title_bar = null;
        this.close_btn = null;
        this.title_container = null;
        this.content = null;
        this.win = null;
        this.scene = null;
        this.dis_wrap = null;
        
        $.extend(this, options);
        
        this.render();
    }
    
    $.osc_ui_win_collection = [];
    
    $.create_window = function(options) {
        var inst = new OSC_UI_Window(options);
        $.osc_ui_win_collection.push(inst);
        
        return inst;
    };
})(jQuery);