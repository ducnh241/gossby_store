<?php

?>
<table class="text-3A">
    <tr>
        <td>
            <div class="font-bold text-2xl text-center">Account Notice: Your Email Changed</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="my-6">
                Hi
                <strong><?= $params['receiver_name'] ?></strong>,
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal">We noticed that you recently changed the email address you use to sign in to <?= $params['shop_name'] ?> account.</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal my-6">
                <strong>Your old email address:</strong> <?= $params['old_email'] ?>
                <br />
                <strong>Your new email address:</strong> <?= $params['email'] ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal">If it was you, you can safely disregard this email. If it wasn’t you, please <a href="mailto:<?= $params['email_cs'] ?>?subject=Account Notice: Your Email Changed">click here</a> to let us know. We will investigate the issue and get right back to you.</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal mt-6">Thank you.</div>
        </td>
    </tr>
</table>