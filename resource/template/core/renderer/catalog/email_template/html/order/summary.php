<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $line_item Model_Catalog_Order_Item */

$order = $params['order'];
$shipping_price_data = $order->getShippingPriceData();

$discount_codes = [];
$saved_price = 0;
$estimated_price = isset($order->data['additional_data']['estimated_price_by_customer_currency']) ? $order->data['additional_data']['estimated_price_by_customer_currency'] : null;
foreach ($order->data['discount_codes'] as $discount_code) {
    $saved_price += $discount_code['discount_price'];

    if ($discount_code['apply_type'] != 'entire_order') {
        continue;
    }

    $discount_codes[] = $discount_code;
}

$listBuyDesign = $order->getBuyDesign();
?>
<table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important;">    
    <tr>
        <td style="padding: 22px 0 !important">
            <div style="<?= $params['css_attributes']['section-title'] ?>"><strong>Your Receipt</strong></div>
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
            <?php foreach ($order->getLineItems() as $line_item) : ?>
                <?php $checkout_summary_tpl_path = OSC::core('observer')->dispatchEvent('catalog/render/email_template/order_summary/item', $line_item, false); ?>
                <?= $this->build($checkout_summary_tpl_path ? $checkout_summary_tpl_path : 'catalog/email_template/html/order/summary/lineItem', ['line_item' => $line_item, 'css_attributes' => $params['css_attributes'], 'is_first_item' => $is_first_item]) ?> 
                <?php $is_first_item = false; ?>
            <?php endforeach; ?>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important;">    
    <tr>
        <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 20px 0 5px 0 !important;">Subtotal:</td>
        <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 20px 0 5px 0 !important;" align="right"><?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['subtotal_price'], 'email_with_currency') ?></td>
    </tr>
    <?php if (count($discount_codes) > 0): ?>
        <?php foreach ($discount_codes as $idx => $discount_code) : ?>
            <tr>
                <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;">Promo Code: <span style="color: #FF5C00 !important;"><?= $discount_code['discount_code'] ?></span></td>
                <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;" align="right">-<?= OSC::helper('catalog/common')->formatPriceByInteger($discount_code['discount_price'], 'email_with_currency') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($listBuyDesign)):
		$buyDesignPrice = $order->getBuyDesignPrice();
		?>
		<tr>
			<td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;">Digital Image ( <?= count($listBuyDesign) ?> Design<?= count($listBuyDesign) > 1 ? 's' : '' ?> ):</td>
			<td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;" align="right">
                <?= OSC::helper('catalog/common')->formatPriceByInteger($buyDesignPrice, 'email_with_currency') ?>
			</td>
		</tr>
	<?php endif; ?>
    <?php if ($shipping_price_data['price'] >= 0): ?>
    <tr>
        <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;">Shipping:</td>
        <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;" align="right">
            <?= OSC::helper('catalog/common')->formatPriceByInteger($shipping_price_data['price'], 'email_with_currency') ?>
            <?php if (isset($shipping_price_data['discount']['discount_code'])) : ?>
                <br /><span style="color: #FF5C00 !important;"><?= $shipping_price_data['discount']['discount_code'] ?></span> - <?= OSC::helper('catalog/common')->formatPriceByInteger($shipping_price_data['discount']['discount_price'], 'email_with_currency', true) ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endif; ?>
</table>
<table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important; margin: 15px 0 !important">   
    <tr>
        <td style="font-family: arial, -apple-system !important; font-size: 1.125rem !important; color: #282364 !important; font-weight: bold !important; padding: 20px 0 5px 0 !important;">Total:</td>
        <td style="font-family: arial, -apple-system !important; font-size: 1.125rem !important; color: #282364 !important; font-weight: bold !important; padding: 20px 0 5px 0 !important;" align="right"><?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['total_price'], 'email_with_currency') ?></td>
    </tr>
    <?php if ($saved_price > 0) : ?>
        <tr>
            <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;">You saved:</td>
            <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;" align="right"><?= OSC::helper('catalog/common')->formatPriceByInteger($saved_price, 'email_with_currency') ?></td>
        </tr>
    <?php endif; ?>
    <?php if ($estimated_price && isset($params['display_estimated_price']) && $params['display_estimated_price']) : ?>
        <tr>
            <td></td>
            <td style="font-family: arial, -apple-system !important; font-size: 1.125rem !important; color: #27ae60 !important; font-weight: bold !important; padding: 20px 0 5px 0 !important;" align="right"><?= $estimated_price ?></td>
        </tr>
    <?php endif; ?>
</table>
<?php if ($order->data['refunded'] > 0) : ?>
    <table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important; margin-bottom: 15px !important">   
        <?php foreach ($order->getTransactionCollection() as $transaction) : ?>
            <?php
            if ($transaction->data['transaction_type'] != 'refund') {
                continue;
            }
            ?>
            <tr>
                <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;">Refund (<?= date('d/m/Y H:i:s') ?>):</td>
                <td style="color: #282364 !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important;" align="right"><?= OSC::helper('catalog/common')->formatPriceByInteger($transaction->data['amount'], 'email_with_currency') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($params['customer_info_render_flag']) : ?>
    <table style="<?= $params['css_attributes']['table'] ?>background: #F6F6F9 !important;">
        <tr>
            <td style="padding: 20px !important">
                <table style="<?= $params['css_attributes']['table'] ?>">
                    <?php if (!isset($params['customer_info_render_just_address_flag']) || !$params['customer_info_render_just_address_flag']) : ?>
                        <tr>
                            <td colspan="2" style="<?= $params['css_attributes']['customer-info-heading'] ?>">Customer information</td>
                        </tr>
                        <tr>
                            <td style="<?= $params['css_attributes']['customer-info-cell-left'] ?>">
                                <div style="<?= $params['css_attributes']['customer-info-title'] ?>">Contact information:</div>
                                <div style="<?= $params['css_attributes']['customer-info-content'] ?>"><?= $order->data['email'] ?></div>
                            </td>
                            <td style="<?= $params['css_attributes']['customer-info-cell-right'] ?>">
                                <div style="<?= $params['css_attributes']['customer-info-title'] ?>">Payment method:</div>
                                <div style="<?= $params['css_attributes']['customer-info-content'] ?>"><?= $order->getPayment()->getTextTitle() ?></div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="<?= $params['css_attributes']['customer-info-cell-left'] ?>">
                            <div style="<?= $params['css_attributes']['customer-info-title'] ?>">Shipping address:</div>
                            <div style="<?= $params['css_attributes']['customer-info-content'] ?>"><?= OSC::helper('catalog/common')->formatAddress($order->getShippingAddress(), '<br />') ?></div>
                        </td>
                        <td style="<?= $params['css_attributes']['customer-info-cell-right'] ?>">
                            <div style="<?= $params['css_attributes']['customer-info-title'] ?>">Billing address:</div>
                            <div style="<?= $params['css_attributes']['customer-info-content'] ?>"><?= OSC::helper('catalog/common')->formatAddress($order->getBillingAddress(true), '<br />') ?></div>
                        </td>
                    </tr>
                    <?php if (!isset($params['customer_info_render_just_address_flag']) || !$params['customer_info_render_just_address_flag']) : ?>
                        <tr>
                            <td style="<?= $params['css_attributes']['customer-info-cell-left'] ?>">
                                <div style="<?= $params['css_attributes']['customer-info-title'] ?>">Shipping method:</div>
                                <div style="<?= $params['css_attributes']['customer-info-content'] ?>"><?= $order->getCarrier()->getRate()->getTitleWithCarrier() ?></div>
                            </td>
                            <td style="<?= $params['css_attributes']['customer-info-cell-right'] ?>">&nbsp;</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>
<?php endif; ?>