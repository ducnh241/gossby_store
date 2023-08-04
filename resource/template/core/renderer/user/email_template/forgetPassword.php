<!DOCTYPE html>
<html lang="en">
    <body>
        <p>Hi <?= $params['username']; ?>,</p>
        <p>We've got a request to change your password for <?= $params['site_name']; ?> Account.</p>
        <p>Your new password is: <b><?= $params['new_password']; ?></b>.</p>
        <p>Please use this password to log in again. If any issue happens or you did not request this, kindly contact your manager for further assistance.</p>
        <p>Regards.</p>
    </body>
</html>
