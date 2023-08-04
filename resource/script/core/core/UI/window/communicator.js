(function($){
    function OSC_UI_Window_Communicator(partner_domain, commands) {        
        this.registerCommand = function(command_key, command_processor) {
            this._command_registry[command_key] = command_processor;
        };
        
        this.sendCommand = function(command_key, command_data) {
            var command = command_key;

            if(typeof command_data != 'undefined') {
                command += ':' + JSON.stringify(command_data);
            }

            window.parent.postMessage(command, this._partner_domain);
        };
        
        this._receiveCommand = function(e) {
            if (e.origin !== this._partner_domain) {
                return;
            }

            var command_key = e.data.replace(/^([a-zA-Z0-9\_]+)(\:+.+)?$/i, '$1').toLowerCase();

            if(typeof this._command_registry[command_key] == 'undefined') {
                return;
            }

            var command_data = e.data.replace(/^([a-zA-Z0-9\_]+)(\:+(.+))?$/i, '$3');

            if(command_data.length > 0) {
                eval('command_data = ' + command_data);
            }

            this._command_registry[command_key](command_data);
        };
        
        var self = this;
        
        this._command_registry = {};
        this._partner_domain = partner_domain;
        
        if(typeof commands == 'object') {
            for(var command_key in commands) {
                if(Object.prototype.toString.call(commands[command_key]) == '[object Function]') {
                    this.registerCommand(command_key, commands[command_key]);
                }
            }
        }
        
        window.addEventListener('message', function(e) { self._receiveCommand(e); }, false);
    }
    
    $.extend($, {registerWinCommunicator : function(partner_domain, commands) { return new OSC_UI_Window_Communicator(partner_domain, commands); }});
})(jQuery);