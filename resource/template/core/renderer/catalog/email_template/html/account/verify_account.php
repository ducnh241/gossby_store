<?php

?>
<table class="text-3A">
    <tr>
        <td>
            <div class="font-bold text-2xl text-center">Activate your <?= $params['shop_name'] ?> Account</div>
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
                Thank you for creating an account with <?= $params['shop_name'] ?>. You are only one step away from getting all the cool benefits, exclusive to our members!
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="text-left mt-4 text-base">For your safety, kindly click the button below to activate your account:</div>
        </td>
    </tr>
    <tr class="text-center">
        <td>
            <a class="button my-6" target="_blank" href="<?= OSC_FRONTEND_BASE_URL . '/account/created-account' . '?token=' . $params['token']; ?>">
                Activate Your Account
            </a>
        </td>
    </tr>
    <tr>
        <td>
            <span class="font-normal mt-8 text-base" style="color: #A7ABB9">This link is only valid for 24 hours. If you have any questions, please <a href="mailto:<?= $params['email_cs'] ?>?subject=Verify your <?= $params['shop_name'] ?> account">click here</a> to shoot us a message, we’ll be happy to assist!</span>
        </td>
    </tr>
</table>