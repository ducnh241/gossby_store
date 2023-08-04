<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $fulfillment Model_Catalog_Order_Fulfillment */
/* @var $line_item Model_Catalog_Order_Item */

$fulfillment = $params['fulfillment'];
$order = $fulfillment->getOrder();
$params['order'] = $order;
?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['h1'] ?>">Some items in your order are on the way!</div>
            <div style="<?= $params['css_attributes']['message'] ?>">
                <p style="<?= $params['css_attributes']['message p'] ?>">Hi <strong><?= $order->getFirstName() ?></strong>!</p>
                <p style="<?= $params['css_attributes']['message p'] ?>">Some items in your order are on the way. Track your shipment to see the delivery status.</p>
            </div>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important;">    
    <tr>
        <td style="padding: 22px 0 !important">
            <div style="<?= $params['css_attributes']['section-title'] ?>"><strong>Items in this shipment</strong></div>
            <div style="<?= $params['css_attributes']['section-desc'] ?>">
                <div style="<?= $params['css_attributes']['section-desc div'] ?>">Order code: <?= $order->data['code'] ?></div>
                <div style="<?= $params['css_attributes']['section-desc div'] ?>">Added date: <?= date('F d, Y h:i A', $order->data['added_timestamp']) ?></div>
            </div>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>">    
    <tr>
        <td>
            <?php $is_first_item = true; ?>
            <?php foreach ($fulfillment->data['line_items'] as $order_line_id => $quantity_data) : ?>
                <?php
                $line_item = $order->getLineItemByItemId($order_line_id);
                $quantity = $quantity_data['fulfill_quantity'] . ($quantity_data['fulfill_quantity'] < $quantity_data['before_quantity'] ? (' of ' . $quantity_data['before_quantity']) : '');
                $checkout_summary_tpl_path = OSC::core('observer')->dispatchEvent('catalog/render/email_template/order_summary/item', $line_item, false);
                ?>
                <?= $this->build($checkout_summary_tpl_path ? $checkout_summary_tpl_path : 'catalog/email_template/html/order/summary/lineItem', ['line_item' => $line_item, 'css_attributes' => $params['css_attributes'], 'is_first_item' => $is_first_item, 'quantity' => $quantity, 'hide_price' => true]) ?> 
                <?php $is_first_item = false; ?>
            <?php endforeach; ?>
            <div style="<?= $params['css_attributes']['font'] ?>"><center>We hope you enjoy the items you're chosen</center></div>
        </td>
    </tr>
</table>
<table class="body" style="<?= $params['css_attributes']['table'] ?>max-width: 400px !important; margin-top: 15px !important; margin-bottom: 30px !important">
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
