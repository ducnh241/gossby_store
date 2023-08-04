<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $line_item Model_Catalog_Order_Item */

$order = $params['order'];
?>
*************************************************
<?= OSC::helper('core/setting')->get('theme/site_name') ?> ( <?= OSC::$base_url ?> )
*************************************************

Order <?= $order->getCode() ?>

----------------------------
Thank you for your purchase!
----------------------------

Hi <?= $order->getFirstName() ?>, we're getting your order ready to be shipped.
We will notify you when it has been sent.

View your order 
( <?= $order->getDetailUrl() ?> )

or Visit our store ( <?= OSC::$base_url ?> )

<?= $this->build('catalog/email_template/text/order/summary', ['order' => $order]) ?>


Customer information
--------------------

<?= $order->getFullName() ?> (<?= $order->data['email'] ?>)

Shipping address
----------------

<?= OSC::helper('catalog/common')->formatAddress($order->getShippingAddress(), "\n") ?>


Billing address
---------------

<?= OSC::helper('catalog/common')->formatAddress($order->getBillingAddress(true), "\n") ?>


Shipping method
---------------

<?= $order->getCarrier()->getRate()->getTitleWithCarrier() ?>


Payment method
--------------

<?= $order->getPayment()->getTextTitle() ?> — <?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['total_price'], 'email_with_currency') ?>


Got questions? Don't hesitate to <a href="<?= OSC::helper('frontend/template')->getContactUrl() . '?open_chat=1' ?>">chat with us.</a>