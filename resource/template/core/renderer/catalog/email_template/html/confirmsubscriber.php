<?php
/* @var $discount_code Model_Catalog_Discount_Code */
?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['message'] ?>text-align: center !important">
                <p style="<?= $params['css_attributes']['message p'] ?>">Hey
                    <strong style="font-weight: bold"><?= $params['subscriber_name'] ?></strong>,
                </p>
                <p style="font-style: normal;font-weight: normal;font-size: 14px;line-height: 16px;text-align: center;/* #282364 */color: #282364;">
                    We have received a request from you to subscribe to news and product updates.<br>
                    Click below to confirm</p>
            </div>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>max-width: 200px !important; margin-top: 30px !important; margin-bottom: 30px !important">
    <tr>
        <td align="center" valign="center">
            <table class="button main-action-cell"
                   style="<?= $params['css_attributes']['table'] ?>;max-width: 180px !important;">
                <tr>
                    <td style="<?= $params['css_attributes']['button-cell'] ?>;background: unset !important;"
                        align="center">
                        <a href="<?= OSC_FRONTEND_BASE_URL . '/postOffice/subscriber/confirm' . '?token=' . $params['token'] ?>"
                           style="<?= $params['css_attributes']['button-cell a'] ?>;background: #25C38C;border: none !important;">
                            Confirm Subscribe
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>max-width: 200px !important; margin-top: 30px !important; margin-bottom: 30px !important">
    <tr>
        <td align="center" valign="center">
            <div style="font-style: normal;font-weight: normal;font-size: 14px;line-height: 16px;color: #282364;text-align: center;margin: 0 auto;width: 600px;background: white;padding:  18px 0 68px 0;">
                Thanks for your choice!
            </div>
        </td>
    </tr>
</table>

