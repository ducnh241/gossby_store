(function($){        
    function _saveCategory(update_url, input) {
        var cat_item = input.closest('li');

        cat_item.removeClass('editing');

        var title_elm = cat_item.find('.title');

        var new_title = input.val();

        input.parent().remove();

        if(new_title == '') {
            return;
        }

        var old_title = title_elm.text();

        title_elm.text(new_title).attr('title', new_title);

        $.ajax({
            type: 'POST',
            url: update_url,
            data: {name : new_title},
            success: function(response){
                if(response.result != 'OK') {
                    title_elm.text(old_title).attr('title', old_title);
                }
            },
            error : function(xhr, ajaxOptions, thrownError) {
                title_elm.text(old_title).attr('title', old_title);
            }
        });
    }
        
    $(document.body).on('click', '.mrk-cat-edit', function(e){
        e.preventDefault();
        e.stopPropagation();
        
        var edit_btn = $(this);
        
        var update_url = edit_btn.attr('href');
        
        var cat_item = edit_btn.closest('li');
        
            
        var input_container = $('<div />').addClass('edit-input');
            
        cat_item.addClass('editing').append(input_container);
            
        var input = $('<input />').attr({type : 'text', value : cat_item.find('.title').text()}).appendTo(input_container);

        input.blur(function(){ _saveCategory(update_url, input); });
        
        input.keydown(function(e){
            if(e.keyCode == 13) {
                e.preventDefault();
                e.stopPropagation();
                
                _saveCategory(update_url, input);
            }
        });
            
        input[0].focus();
        input[0].select();
    });
})(jQuery);