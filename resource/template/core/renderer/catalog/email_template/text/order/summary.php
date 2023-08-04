<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $line_item Model_Catalog_Order_Item */

$order = $params['order'];
$shipping_price_data = $order->getShippingPriceData();
$discount_codes = [];
$saved_price = 0;
foreach ($order->data['discount_codes'] as $discount_code) {
    $saved_price += $discount_code['discount_price'];
    
    if ($discount_code['apply_type'] != 'entire_order') {
        continue;
    }
    
    $discount_codes[] = $discount_code;
}
$listBuyDesign = $order->getBuyDesign();
?>
-------------
Order summary
-------------
<?php foreach ($order->getLineItems() as $line_item) : ?>

<?= $line_item->data['title'] ?> x <?= $line_item->data['quantity'] ?>
<?php if (count($line_item->data['options']) > 0) : ?>
    
<?= $line_item->getVariantOptionsText() ?>
<?php endif; ?>
<?php if ($line_item->data['sku']) : ?>

SKU: <?= $line_item->data['sku'] ?>
<?php endif; ?>
<?php if ($line_item->data['refunded_quantity'] > 0) : ?>

Refunded
<?php endif; ?>
                                        
<?= OSC::helper('catalog/common')->formatPriceByInteger($line_item->getAmountWithDiscount(), 'email_with_currency',true) ?><?php if ($line_item->data['discount']) : ?> (<?= OSC::helper('catalog/common')->formatPriceByInteger(-$line_item->data['discount']['discount_price'], 'email_with_currency') ?> by <?= $line_item->data['discount']['discount_code'] ?>)<?php endif; ?>

<?php endforeach; ?>

Subtotal
--------

<?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['subtotal_price'], 'email_with_currency') ?>

<?php if(count($discount_codes) > 0): ?>
    Promo code
    --------
    <?php foreach($discount_codes as $discount_code) : ?>

        <?= OSC::helper('catalog/common')->formatPriceByInteger(-$discount_code['discount_price'], 'email_with_currency') ?> by <?= $discount_code['discount_code'] ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($listBuyDesign)):
    $buyDesignPrice = $order->getBuyDesignPrice();
    ?>
    Design: (<?= count($listBuyDesign) ?> Design )
    --------
    <?= OSC::helper('catalog/common')->formatPriceByInteger($buyDesignPrice, 'email_with_currency') ?>
<?php endif; ?>

Shipping
--------

<?= OSC::helper('catalog/common')->formatPriceByInteger($shipping_price_data['price'], 'email_with_currency') ?><?php if(isset($shipping_price_data['discount']['discount_code'])) : ?> (<?= OSC::helper('catalog/common')->formatPriceByInteger(-$shipping_price_data['discount']['discount_price'], 'email_with_currency',true) ?> by <?= $shipping_price_data['discount']['discount_code'] ?>)<?php endif; ?>


Total
-----

<?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['total_price'], 'email_with_currency') ?>
<?php if($saved_price > 0) : ?>


You saved <?= OSC::helper('catalog/common')->formatPriceByInteger($saved_price, 'email_with_currency') ?>
<?php endif; ?>
<?php if ($order->data['refunded'] > 0) : ?>
<?php foreach ($order->getTransactionCollection() as $transaction) : ?>
<?php
if ($transaction->data['transaction_type'] != 'refund') {
    continue;
}
?>


Refund (<?= date('d/m/Y H:i:s') ?>)
----------------------

<?= OSC::helper('catalog/common')->formatPriceByInteger($transaction->data['amount'], 'email_with_currency') ?>
<?php endforeach; ?>
<?php endif; ?>