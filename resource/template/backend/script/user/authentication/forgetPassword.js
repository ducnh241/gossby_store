(function ($) {
    const error_scene = $('.error-wrap');
    const send_reset_password_email_button = $('#send-reset-password-email-button');

    // check form submitted flag
    let is_reset_password_form_submitted = false;

    // declare error message
    $.lang.auth_error_email_empty = '';
    $.lang.auth_error_email_incorrect = '';

    if (error_scene.html()) {
        error_scene.slideDown();
    }

    function showErrorMessage(error_message) {
        // show error scene
        error_scene.html(error_message).slideDown();
    }

    /**
     * @param request_data
     * @returns {boolean}
     */
    function validateRequestData(request_data) {
        if (!request_data.email) {
            showErrorMessage($.lang.auth_error_email_empty);
            return false;
        }

        if (!$.validator.validEmail(request_data.email)) {
            showErrorMessage($.lang.auth_error_email_incorrect);
            return false;
        }

        return true;
    }

    function handleSendForgetPasswordEmail(url, request_data) {
        $.ajax({
            type: 'POST',
            url: url,
            data: request_data,
            success: function (response) {
                if (response.result === 'OK') {
                    if (response.data && response.data.message) {
                        // show success scene
                        error_scene.addClass('success-wrap-background');
                        showErrorMessage(response.data.message);

                        // disable submit button
                        send_reset_password_email_button.prop('disabled', true);

                        // change form submitted flag
                        is_reset_password_form_submitted = true;
                    }
                } else {
                    showErrorMessage(response.message);
                    return false;
                }
            }
        });
    }

    // handle submit form
    $('#auth-frm').submit(function () {
        // check form submitted flag
        if (is_reset_password_form_submitted) {
            return false;
        }

        const submit_url = $(this).attr('action');

        // clear and hide error scene
        error_scene.html('').slideUp().removeClass('success-wrap-background');

        const request_data = {
            email: $('#auth-email').getVal()
        };

        // validate request data
        const is_validated = validateRequestData(request_data);

        if (!is_validated) {
            return false;
        }

        // handle send forget password email
        handleSendForgetPasswordEmail(submit_url, request_data);

        return false;
    });
})(jQuery);
