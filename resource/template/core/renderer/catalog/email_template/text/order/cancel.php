<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $line_item['model'] Model_Catalog_Order_Item */

$order = $params['order'];
?>
*************************************************
<?= OSC::helper('core/setting')->get('theme/site_name') ?> ( <?= OSC::$base_url ?> )
*************************************************

Hi <?= $order->getBillingFullName() ?>

Order <?= $order->getCode() ?> was canceled and your payment has been voided.

Total amount refunded: <?= OSC::helper('catalog/common')->formatPriceByInteger($order->data['total_price'], 'email_with_currency') ?>


<?= $this->build('catalog/email_template/text/order/summary', ['order' => $order]) ?>


---------------------

Got questions? Don't hesitate to <a href="<?= OSC::helper('frontend/template')->getContactUrl() . '/?open_chat=1' ?>">chat with us.</a>