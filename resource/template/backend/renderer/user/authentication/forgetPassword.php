<?php
/* @var $this Helper_Backend_Template */;

// get language data
$lang = OSC::core('language')->get();

$this->push('user/authentication.css', 'css');
$this->push('[core]core/validate.js', 'js');
$this->push('user/authentication/forgetPassword.js', 'js');

// declare jquery variables
$this->push(<<<EOF
jQuery.lang.auth_error_email_empty = '{$lang['usr.err_email_empty']}';
jQuery.lang.auth_error_email_incorrect = '{$lang['usr.err_email_incorrect']}';
EOF
, 'js_code');

// hide sidebar
$this->register('EMPTY_PAGE', 1)
    // hide breadcrumb
    ->register('NO_BREADCRUMB', 1);
?>
<div class="auth-frm-wrap">
    <div class="tile"></div>
    <h2><?= $lang['backend.welcome']; ?></h2>
    <div class="desc"><?= $lang['usr.forget_password_description']; ?></div>
    <div class="error-wrap"><?php if (!empty($params['error'])) { echo $params['error']; } ?></div>
    <form method="post" id="auth-frm" action="<?= $this->getUrl('user/backend_authentication/sendForgetPasswordEmail') ?>" autocomplete="off">
        <div>
            <input type="text" class="input-text input-large" id="auth-email" name="email" placeholder="<?= $lang['usr.your_email']; ?>">
        </div>
        <div>
            <button id="send-reset-password-email-button" type="submit" class="btn btn-primary mt15">
                <?= $lang['usr.auth_forget_password']; ?>
            </button>
        </div>
    </form>
    <div class="sign-in-link">
        <a href="<?= $this->getUrl('user/backend_authentication/index') ?>"><?= $lang['usr.auth_sign_in'] ?></a>
    </div>
</div>
