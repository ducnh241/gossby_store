<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $fulfillment Model_Catalog_Order_Fulfillment */
/* @var $line_item Model_Catalog_Order_Item */

$fulfillment = $params['fulfillment'];
$order = $fulfillment->getOrder();
?>
*************************************************
<?= OSC::helper('core/setting')->get('theme/site_name') ?> ( <?= OSC::$base_url ?> )
*************************************************

Order <?= $order->getCode() ?>

---------------------------------------
Some items in your order are on the way
---------------------------------------

Some items in your order are on the way. Track your shipment to see the delivery status.

View your order 
( <?= $order->getDetailUrl() ?> )

or Visit our store ( <?= OSC::$base_url ?> )

Items in this shipment
----------------------

<?php foreach ($fulfillment->data['line_items'] as $variant_id => $quantity_data) : ?>
<?php $line_item = $order->getLineItemByVariantId($variant_id); ?>
<?= $line_item->data['title'] ?> x <?= $quantity_data['fulfill_quantity'] ?><?php if($quantity_data['fulfill_quantity'] < $quantity_data['before_quantity']) : ?> of <?= $quantity_data['before_quantity'] ?><?php endif; ?>
<?php if (count($line_item->data['options']) > 0) : ?>
    
<?= $line_item->getVariantOptionsText() ?>
<?php endif; ?>
<?php if ($line_item->data['sku']) : ?>

SKU: <?= $line_item->data['sku'] ?>
<?php endif; ?>


<?php endforeach; ?>
---------------------

Got questions? Don't hesitate to <a href="<?= OSC::helper('frontend/template')->getContactUrl() . '?open_chat=1' ?>">chat with us.</a>