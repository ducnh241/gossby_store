(function($) {
    function OSC_Input(elm, opts) {
        this._render = function() {
            this.container = $('<div />').addClass('osc-input');    
            
            if(this.size == 'large') {
                this.container.addClass('large');
            }
            
            this.input.before(this.container);
            this.container.append($('<div />').append(this.input));
            
            if(this.icon) {
                if(typeof this.icon == 'string') {
                    this.icon = $('<i />').addClass(this.icon);
                }    
                
                this.container.addClass('has-icon').find('> div').before(this.icon);
            }
            
            if(this.label) {
                if(this.input.val() == '') {
                    this.input.val(this.label);
                    this.container.addClass('has-label');
                }
            }
        }
        
        this._initialize = function() {
            var self = this;
            
            this.input.data('custom_method__getVal',  function() {
                var val = $(this[0]).val();

                if(self.label && val == self.label) {
                    return '';
                }

                return val;
            });
            
            if(opts.width && opts.width > 0) {
                this.container.width(opts.width);
            }
            
            if(this.type == 'password' && this.label && this.label == this.input.val()) {
                this.input.prop('type', 'text');
            }
            
            this.container.click(function() {
                self.input.focus();
            });
            
            this.input.keydown(function(e) {
                if(self.label && self.input.val() == self.label) {                       
                    if(self.type == 'password') {
                        self.input.prop('type', 'password');
                    }
                    
                    self.input.val('').removeClass('pre-enter');
                }
            }).keyup(function() {
                if(self.label && self.input.val() == '') {                    
                    if(self.type == 'password') {
                        self.input.prop('type', 'text');
                    }
                    
                    self.input.val(self.label).addClass('pre-enter');     

                    if (this.setSelectionRange) {
                        this.focus();
                        this.setSelectionRange(0, 0);
                    } else if (this.createTextRange) {
                        var range = this.createTextRange();

                        range.collapse(true);
                        range.moveStart('character', 0);
                        range.moveEnd('character', 0);

                        range.select();
                    }
                }    
            }).mouseup(function() {
                if(self.label && self.input.val() == self.label) {              
                    if (this.setSelectionRange) {
                        this.setSelectionRange(0,0);
                    } else if (this.createTextRange) {  
                        var range = this.createTextRange();

                        range.collapse(true);
                        range.moveStart('character', 0);
                        range.moveEnd('character', 0);

                        range.select();
                    }
                }    
            }).focus(function() {    
                self.container.addClass('focus').find('i').removeClass('gray').addClass('blue');
                
                if(self.label && self.input.val() == self.label) { 
                    self.input.addClass('pre-enter');
                    
                    if (this.setSelectionRange) {
                        this.setSelectionRange(0,0);
                    } else if (this.createTextRange) {  
                        var range = this.createTextRange();

                        range.collapse(true);
                        range.moveStart('character', 0);
                        range.moveEnd('character', 0);

                        range.select();
                    }
                }
            }).blur(function() {
                self.container.removeClass('focus').find('i').addClass('gray').removeClass('blue');
                                                
                if(self.label) {
                    if(self.input.val() == '') {
                        self.input.val(self.label);
                    }
                    
                    if(self.input.val() == self.label) {
                        self.container.addClass('has-label');
                        
                        if(self.type == 'password') {
                            self.input.prop('type', 'text');
                        }
                    } else {
                        self.container.removeClass('has-label');
                    }
                    
                    self.input.removeClass('pre-enter');
                }
            });
        }
        
        this._detectType = function() {
            if(! this.type) {
                this.type = this.input.attr('type');
                
                if(! this.type) {
                    this.type = 'text';
                }
            }

            this.type = this.type.toLowerCase();
        }
        
        if(typeof opts != 'object') {
            opts = {};
        }
        
        this.input = null;
        this.container = null;
        this.icon = null;
        this.size = 'normal';
        
        opts.input = $(elm);
        
        $.extend(this, opts);
        
        if(! opts.width) {
            opts.width = opts.input.width();
            opts.width = parseInt(opts.width);
            
            if(isNaN(opts.width) || opts.width < 1) {
                opts.width = 0;
            }
        }
        
        if(this.input[0].tagName !== 'INPUT') {
            this.container = this.input;
            this.input = this.container.find('input');
            this.icon = this.container.find('i');
            
            if(! this.icon[0]) {
                this.icon = null;
            }
            
            this.size = this.container.hasClass('large') ? 'large' : 'normal';
            
            this._detectType();
        } else {        
            this._detectType();
            this._render();
        }
        
        this._initialize();
    }
    
    $.fn.osc_ui_form_input = function() {
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
            var instance = $(this).data('osc-input');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-input', new OSC_Input(this, opts));
                }
            }
        });
    };
            
    $(document).on('insert', '.mrk-osc-input-init', function(e) {        
        $(this).removeClass('mrk-osc-input-init').osc_ui_form_input();
    });
})(jQuery);