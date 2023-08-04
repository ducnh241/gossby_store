(function($) {
    function OSC_JSON() {
        this.serialize = function(arr) {
            return JSON.stringify(arr);
        };
	
        this.unserialize = function(s) {
            try {
                return eval('(' + s + ')');
            } catch(ex) {
                
            }
        };
        
        this.encode = function(arr) {
            return this.serialize(arr);
        };
        
        this.decode = function(s) {
            return this.unserialize(s);
        };
    }
    
    $.JSON = new OSC_JSON();
    
    $.extend($, {
        json_encode : function(data) {
            return $.JSON.encode(data);
        },        
        json_decode : function(data) {
            return $.JSON.decode(data);
        }
    });
})(jQuery);