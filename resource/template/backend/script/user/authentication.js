(function($) {
    var err_scene = $('.error-wrap');
    
    $.lang.auth_err_email_empty = '';
    $.lang.auth_err_password_empty = '';
    $.lang.auth_err_email_incorrect = '';
    
    if(err_scene.html()) {
        err_scene.slideDown();
    }
    
    function _AUTH_ERROR(error_message) {
        err_scene.html(error_message).slideDown();
    }
    
    $('#auth-frm').submit(function() {
        err_scene.slideUp();
        
        var data = {
            email : $('#auth-email').getVal(),
            password : $('#auth-password').getVal()
        }
        
        if(! data.email) {
            _AUTH_ERROR($.lang.auth_err_email_empty);
            return false;
        }
        
        if(! data.password) {
            _AUTH_ERROR($.lang.auth_err_password_empty);
            return false;
        }
        
        if(! $.validator.validEmail(data.email)) {
            _AUTH_ERROR($.lang.auth_err_email_incorrect);
            return false;
        }
        
        $.ajax({
            type : 'POST',
            url : $(this).attr('action'),
            data : data,
            success : function(response) {                
                if(response.result != 'OK') {
                    _AUTH_ERROR(response.message);
                    return false;
                }
                
                window.location = response.data;
            },
            error : function() {
        
            }
        });
        
        return false;
    });
})(jQuery);