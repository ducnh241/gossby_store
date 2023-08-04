<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */

$order = $params['order'];
?>
<table style="max-width: 400px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td align="center" valign="center">
            <div style="<?= $params['css_attributes']['h1'] ?>">Information for order <?= $order->getCode() ?>!</div>
            <div style="<?= $params['css_attributes']['message'] ?>">
                <p style="<?= $params['css_attributes']['message p'] ?>"><?= nl2br($this->safeString($params['message'])) ?></p>
            </div>
        </td>
    </tr>
</table>
<?= $this->build('catalog/email_template/html/order/summary', array_merge($params, ['customer_info_render_flag' => true])) ?>
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