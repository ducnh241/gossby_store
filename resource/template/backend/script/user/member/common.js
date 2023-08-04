(function ($) {
    'use strict';

    var AUTH_SECRET_CONTAINER = null;

    window.initUserMemberLoadAuthSecretFrm = function () {
        $(this).click(function () {
            var btn = $(this);

            if (btn.attr('disabled') === 'disabled') {
                return;
            }

            btn.attr('disabled', 'disabled');

            $.ajax({
                url: btn.attr('uri'),
                type: 'post',
                success: function (response) {
                    btn.removeAttr('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    var win = null;

                    AUTH_SECRET_CONTAINER = $('<div />').addClass('win-simple-frm').width(400);

                    AUTH_SECRET_CONTAINER.html(response.data.html);

                    $.wrapContent($('<div />'), {key: 'userAuthSecretFrm', close_callback: function () {
                            win.destroy(null, true);
                            AUTH_SECRET_CONTAINER = null;
                        }});

                    win = $.create_window({
                        destroy_hook: function () {
                            $.unwrapContent('userAuthSecretFrm');
                        },
                        title: 'Authenticator secret for ' + btn.attr('username'),
                        content: AUTH_SECRET_CONTAINER
                    });
                }
            });
        });
    };

    window.initUserMemberAuthSecretKeyAction = function () {

        $(this).click(function () {
            var btn = $(this);

            if (btn.attr('disabled') === 'disabled') {
                return;
            }

            btn.attr('disabled', 'disabled');

            $.ajax({
                url: btn.attr('act-uri'),
                type: 'post',
                success: function (response) {
                    btn.removeAttr('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    AUTH_SECRET_CONTAINER.html(response.data.html);
                }
            });
        });
    };

    $(document).ready(function () {
        try {
            $('.select_user_group').select2({
                placeholder: "Select permission mask"
            });
        } catch (e) {

        }
        var toggled = false;
        $('.btn-icon-eye').click(function () {
            toggled = !toggled;
            $(this).parent('.input-password').find('input').prop('type', toggled ? 'text' : 'password')
        })
    })

})(jQuery);