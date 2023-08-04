(function($) {   
    var app_list = $('#app-list');
    var wrap = app_list.find('.scene > .wrap');
    var item_list = wrap.find('> ul');
    var next_btn = app_list.find('.next-btn');
    var prev_btn = app_list.find('.prev-btn');
    var filter_btn = app_list.find('.filter button');
    var filter_frm = app_list.find('.filter input');
    
    item_list.find('a').each(function(){
        var keywords = $(this).attr('title');
        
        keywords = keywords.replace(/^\s+|\s+$/g, '');
        keywords = keywords.replace(/[^a-zA-Z0-9 ]/g, '');
        
        $(this).data('keywords', keywords);
    });
    
    filter_frm.focus(function(){
        $(this).parent().addClass('focus');
        item_list.css({top : '0px'});        
        setTimeout(function(){ $.setupAppList(); }, 400);  
    });
    filter_frm.blur(function(){
        $(this).parent().removeClass('focus');
    });
    filter_frm.keyup(filter);
    filter_btn.click(function(){ filter(); return false; });
    
    var _FILTER_RECALL_FLAG = false;
    var _FILTER_LOCK_FLAG = false;
    var _FILTER_LAST_KEYWORDS = '';
    
    function filter() {
        if(_FILTER_LOCK_FLAG) {
            _FILTER_RECALL_FLAG = true;
            return;
        }
        
        _FILTER_LOCK_FLAG = true;
        
        _FILTER_RECALL_FLAG = false;
        
        var keywords = filter_frm.val().replace(/^\s+|\s+$/g, '');
        
        keywords = keywords.replace(/[^a-zA-Z0-9 ]/g, '');
        
        if(keywords != _FILTER_LAST_KEYWORDS) {
            _FILTER_LAST_KEYWORDS = keywords;
            
            if(keywords != '') {            
                var regex = new RegExp(keywords.replace(/\s+/g, '|'), 'i');

                item_list.find('li').each(function(){
                    var item_keywords = $(this).find('a').data('keywords');

                    if(regex.test(item_keywords)) {
                        $(this).removeClass('deactive');
                    } else {
                        $(this).addClass('deactive');
                    }
                });
            } else {
                item_list.find('li').removeClass('deactive');
            }
        
            $.setupAppList();
        }
    
        _FILTER_LOCK_FLAG = false;
        
        if(_FILTER_RECALL_FLAG) {
            filter();
        }
    }
    
    next_btn.click(function(){
        if(! next_btn.hasClass('active')) {
            return;
        }
        
        var new_top = item_list.position().top - wrap.height();

        if(Math.abs(new_top) > item_list.height() - wrap.height()) {
            new_top = wrap.height() - item_list.height();
        }
        
        item_list.css({top : new_top + 'px'});
        
        setTimeout(function(){ $.setupAppList(); }, 400);     
    });
    
    prev_btn.click(function(){
        if(! prev_btn.hasClass('active')) {
            return;
        }
        
        var new_top = item_list.position().top + wrap.height();
        
        if(new_top > 0) {
            new_top = 0;
        }
        
        item_list.css({top : new_top + 'px'});
        
        setTimeout(function(){ $.setupAppList(); }, 400);        
    });
        
    $.setupAppList = function() {        
        if(item_list.position().top < 0) {
            prev_btn.addClass('active');
        } else {
            prev_btn.removeClass('active');
        }
        
        if(Math.abs(item_list.position().top) < item_list.height() - wrap.height()) {
            next_btn.addClass('active');
        } else {
            next_btn.removeClass('active');
        }
    }
})(jQuery);