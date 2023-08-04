<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $line_item['model'] Model_Catalog_Order_Item */

$order = $params['order'];
?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['h1'] ?>">Order Cancellation Notification!</div>
            <div style="<?= $params['css_attributes']['message'] ?>">
                <p style="<?= $params['css_attributes']['message p'] ?>">Hi <strong><?= $order->getFirstName() ?></strong>!</p>
                <p style="<?= $params['css_attributes']['message p'] ?>">Your order has been canceled for the reasons indicated. The items listed below part of the canceled order</p>
            </div>
        </td>
    </tr>
</table>
<?= $this->build('catalog/email_template/html/order/summary', $params) ?>
<table class="body" style="<?= $params['css_attributes']['table'] ?>max-width: 400px !important; margin-top: 30px !important; margin-bottom: 30px !important">
    <tr>
        <td align="center" valign="center">
            <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                <tr>
                    <td style="<?= $params['css_attributes']['button-cell'] ?>" align="center"><a href="<?= OSC::helper('postOffice/email')->getClickUrl($order->getDetailUrl()) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>">View your order</a></td>
                    <td style="width: 15px !important">&nbsp;</td>
                    <td style="<?= $params['css_attributes']['button-cell-outline'] ?>" align="center"><a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC_FRONTEND_BASE_URL) ?>" style="<?= $params['css_attributes']['button-cell-outline a'] ?>">Visit Our Store</a></td>
                </tr>
            </table>
        </td>
    </tr>
</table>