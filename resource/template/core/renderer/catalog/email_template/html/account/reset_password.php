<?php

?>
<table class="text-3A">
    <tr>
        <td>
            <div class="font-bold text-2xl text-center">Reset your <?= $params['shop_name'] ?> Password</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="my-4">
                Hi
                <strong><?= $params['receiver_name'] ?></strong>,
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-left text-base">
                We received your request to reset the password for your <?= $params['shop_name'] ?> account. Please click the button below to finish the reset process:
            </div>
        </td>
    </tr>
    <tr class="text-center">
        <td>
            <a class="button my-6" target="_blank" href="<?= OSC_FRONTEND_BASE_URL . '/account/reset-password' . '?token=' . $params['token']; ?>">
                Reset Password
            </a>
        </td>
    </tr>
    <tr>
        <td>
            <div>
                If you did not request any password reset, you can safely ignore this email. Otherwise, the reset link will expire after 24 hours.
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="font-normal mt-6 text-base">Please <a href="mailto:<?= $params['email_cs'] ?>?subject=Reset your <?= $params['shop_name'] ?> password">click here</a> if you need further assistance.</div>
        </td>
    </tr>
</table>