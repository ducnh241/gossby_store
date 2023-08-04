(function($) {
    OSC_Uploader_Renderer_Multiple = function() {
        
    }
    
    $.extend(OSC_Uploader_Renderer_Multiple.prototype, {
        inst : null,
        btn : null,
        drag_drop_area : null,
        items : {},
        file_list : null,
        lang : {click_to_upload : 'Click to upload', drop_file_here : 'Drop files here to upload'},
        
        setInstance : function(inst) {
            this.inst = inst;
            return this;
        },
        
        render : function() {
            $(this.inst.container).addClass('osc-uploader').html('<div class="upload-btn mrk-input-container">' + this.lang.click_to_upload + '</div><div class="drag-drop-area mrk-drag-drop-area"><span>' + this.lang.drop_file_here + '</span></div><ul class="file-list mrk-file-list"></ul>');
            this.btn = $('.mrk-input-container', this.inst.container)[0];
            this.file_list = $('.mrk-file-list', this.inst.container)[0];
            this.drag_drop_area = $('.mrk-drag-drop-area', this.inst.container)[0];
            return this;
        },  
        
        buttonMouseOverHook : function(e) {            
            $(this.btn).addClass('hover');            
        },
        
        buttonMouseOutHook : function(e) {
            $(this.btn).removeClass('hover');             
        },
        
        buttonFocusHook : function(e) {
            $(this.btn).addClass('focus');          
        },
        
        buttonBlurHook : function(e) {
            $(this.btn).removeClass('focus');          
        },
        
        addQueue : function(id) {
            var item = {
                progress : null,
                xhr_supported : false
            };
            
            item.root = $.createElement('li', {
                className : 'mrk-file-item', 
                idx : id
            }, {}, this.file_list);
                
            item.name = $.createElement('span', {
                innerHTML : $.osc_uploader.getFileName(this.inst, id, true), 
                className : 'name mrk-name'
            }, {}, item.root);
            item.size = $.createElement('span', {
                innerHTML : '(' + $.osc_uploader.getFileSize(this.inst, id, true) + ')', 
                className : 'size mrk-size'
            }, {}, item.root);
            item.action = $.createElement('span', {
                innerHTML : '<span>Remove</span>', 
                className : 'action mrk-action'
            }, {}, item.root);
            item.state = $.createElement('span', {
                innerHTML : 'queue', 
                className : 'state mrk-state'
            }, {}, item.root);            
            
            this.items[id] = item;
            
            return item.root;
        },
        
        uploadHook : function(id, xhr_supported) {
            var item = this.items[id];
            
            item.xhr_supported = xhr_supported;
                
            $('span', item.action).html('Cancel');
            $(item.state).remove();
                
            item.progress = $.createElement('div', {
                className : 'progress mrk-progress'
            }, {}, item.root);
                                
            $.createElement('div', {}, {}, item.progress);
            
            return item.root;
        },
        
        uploadProgressHook : function(id, total, loaded) {
            $($('div', this.items[id].progress)).css('width', (loaded*100/total) + '%');            
        },
        
        uploadCompleteHook : function(id, response) {
            $(this.items[id].progress).remove();
            
            return this.items[id].root;
        },
        
        remove : function(id) {
            $(this.items[id].root).remove();
            this.items[id] = null;
        },
        
        hideUploadButton : function() {
            $(this.btn).hide();
        },
        
        showUploadButton : function() {
            $(this.btn).show();
        },
        
        showDragDropArea : function() {
            $(this.drag_drop_area).show();                      
        },
        
        hideDragDropArea : function() {
            $(this.drag_drop_area).hide();             
        },
        
        dragDropAreaEnterHook : function() {
            $(this.drag_drop_area).addClass('drop-active');
        },
        
        dragDropAreaLeaveHook : function() {
            $(this.drag_drop_area).removeClass('drop-active');         
        }
    });
    
    $.OSC_Uploader_Renderer_Multiple = OSC_Uploader_Renderer_Multiple;
})(jQuery);