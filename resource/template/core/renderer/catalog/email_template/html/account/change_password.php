<?php

?>
<table class="text-3A">
    <tr>
        <td>
            <div class="font-bold text-2xl text-center">Account Notice: Password Updated</div>
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
            <div class="text-base font-normal">Your account password has been changed successfully as requested.</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal my-6">If it was you, you can safely disregard this email. If you didn’t ask to change your password, please <a href="mailto:<?= $params['email_cs'] ?>?subject=Account Notice: Password updated">click here</a> to let us know. We are here to keep your account secure.</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal">Thank you.</div>
        </td>
    </tr>
</table>