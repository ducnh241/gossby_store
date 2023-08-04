<?php

?>
<table class="text-3A">
    <tr>
        <td>
            <div class="font-bold text-2xl text-center">Confirm your new Email Address</div>
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
                You recently requested to change the email address you use to sign in to your <?= $params['shop_name'] ?> account. To finish, please click the link below:
            </div>
        </td>
    </tr>
    <tr class="text-center">
        <td>
            <a class="button my-6" target="_blank" href="<?= OSC_FRONTEND_BASE_URL . '/account/change-email' . '?token=' . $params['token']; ?>">
                Confirm Email Address
            </a>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-bold">Kindly note that this link is only valid for 24 hours.</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-normal my-6">If you didn’t ask to change the email address, please <a href="mailto:<?= $params['email_cs'] ?>?subject=Confirm your new Email Address">click here</a> to let us know. We are here to keep your account secure.</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-base font-bold">Thank you.</div>
        </td>
    </tr>
</table>