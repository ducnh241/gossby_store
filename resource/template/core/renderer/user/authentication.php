<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Muli:wght@400;700&display=swap">
    <style type="text/css">
        *{margin: 0;padding: 0;box-sizing: border-box}
        body {padding: 0 !important;min-height: initial;background: url('<?= $this->getFile('renderer/user/image/bg-login.jpg') ?>') no-repeat 50% 50% !important;background-size: cover;font-family: 'Muli', sans-serif}
        .login {height: 100vh;display: flex;align-items: center;justify-content: center}

        .login_inner {background: white;width: 100%;max-width: 415px;margin: 0 15px;padding: 40px;box-shadow: 0 5px 30px rgba(55, 81, 255, 0.15);border-radius: 5px;min-height: 440px;}
        .login_header { margin: 0 auto 25px;text-align: center}
        .login_header img {width: 102px;margin: 0 0 15px}
        .login_title {color: #282364;font-size: 16px;text-align: center;}


        .login_field {margin-bottom: 10px;position: relative;}
        .login_field input{width: 100%;border: solid 1px #E0E0E0;min-height: 35px;font-style: normal;padding: 5px 10px;border-radius: 3px;font-family: 'Muli', sans-serif}
        .login_field input::placeholder{color: #E0E0E0}
        .login_field input:focus{border-color: #1688FA;outline: none}

        .login_submit {background: #1688FA;color: white;width: 100%;height:40px;line-height: 40px;font-weight: 700;font-size: 14px;border-radius: 3px;outline: none;border: none;cursor: pointer;font-family: 'Muli', sans-serif;margin: 10px 0 0}
        .error_wrap{background: rgba(234, 105, 105, 0.65);color: #fff;padding: 10px;margin-bottom: 20px;display: none;}
        @media (max-width: 320px) {
            .login_inner {padding: 15px}
        }
    </style>
    <script type="text/javascript" src="<?= OSC::$base_url. '/resource/script/community/jquery/jquery-1.7.2.js' ?>"></script>
    <script >
        $(document).ready(function ($) {
            var err_scene = $('.error_wrap');

            console.log(err_scene);

            if(err_scene.html()) {
                err_scene.slideDown();
            }

            function _AUTH_ERROR(error_message) {
                err_scene.html(error_message).slideDown();
            }

            $('#auth_frm').submit(function(event) {
                err_scene.slideUp();

                var data = {
                    email: $('#auth-email').val(),
                    password: $('#auth-password').val()
                }

                if (!data.email) {
                    _AUTH_ERROR('The email is empty');
                    return false;
                }

                if (!data.password) {
                    _AUTH_ERROR('The password is empty');
                    return false;
                }

                if (!IsEmail(data.email)) {
                    _AUTH_ERROR('The email is incorrect');
                    return false;
                }

                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: data,
                    success: function (response) {
                        if (response.result != 'OK') {
                            _AUTH_ERROR(response.message);
                            return false;
                        }

                        window.location = response.data.url;
                    },
                    error: function () {

                    }
                });

                return false;
            });

            function IsEmail(email) {
                var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if(!regex.test(email)) {
                    return false;
                }else{
                    return true;
                }
            }
        });
    </script>
</head>
<body>
<div class="container">
    <div class="login">
        <div class="login_inner">
            <div class="login_header">
                <img src="<?= $this->getFile('renderer/user/image/login.svg') ?>" alt=""/>
                <h2 class="login_title">Login With Your Account</h2>
            </div>
            <div class="error_wrap"><?php if ($params['error']): ?><?php echo $params['error']; ?><?php endif; ?></div>
            <div class="login_body">
                <form method="post" class="login_frm" id="auth_frm" action="<?php echo $this->getUrl(null, array('act' => 'process')); ?>" autocomplete="off">
                    <div class="login_field"><input type="text" class="input-text input-large" name="email" id="auth-email" placeholder="Email" /></div>
                    <div class="login_field"><input type="password" class="input-text input-large" name="password" id="auth-password" placeholder="Password" /></div>
                    <div><button type="submit" class="login_submit">Login</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>