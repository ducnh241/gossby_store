<?php
/* @var $this Helper_Backend_Template */
?>
<?php
$lang = OSC::core('language')->get();

$this->push('user/authentication.css', 'css');
$this->push('[core]core/validate.js', 'js');
$this->push('user/authentication.js', 'js');

$this->push(<<<EOF
jQuery.lang.auth_err_email_empty = '{$lang['usr.err_email_empty']}';
jQuery.lang.auth_err_password_empty = '{$lang['usr.err_password_empty']}';
jQuery.lang.auth_err_email_incorrect = '{$lang['usr.err_email_incorrect']}';
EOF
, 'js_code');

$this->register('EMPTY_PAGE', 1)->register('NO_BREADCRUMB', 1);
?>
<div class="auth-frm-wrap">
    <div class="tile"></div>
    <h2><?php echo $lang['backend.welcome']; ?></h2>
    <div class="desc"><?php echo $lang['usr.backend_auth_desc']; ?></div>
    <div class="error-wrap"><?php if ($params['error']): ?><?php echo $params['error']; ?><?php endif; ?></div>
    <form method="post" id="auth-frm" action="<?php echo $this->getUrl(null, array('act' => 'process')); ?>" autocomplete="off">
        <div><input type="text" class="input-text input-large" name="email" id="auth-email" placeholder="<?php echo $lang['usr.your_email']; ?>" /></div>
        <div><input type="password" class="input-text input-large" name="password" id="auth-password" placeholder="<?php echo $lang['usr.your_password']; ?>" /></div>
        <div><button type="submit" class="btn mt15 btn-primary"><?php echo $lang['usr.auth_sign_in']; ?></button></div>
    </form>
    <div class="forgot-password-link">
        <a href="<?= $this->getUrl('user/backend_authentication/showForgetPasswordForm') ?>"><?= $lang['usr.auth_forgot_password']; ?></a>
    </div>
</div>
