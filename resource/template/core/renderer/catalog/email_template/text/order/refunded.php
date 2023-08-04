<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $transaction Model_Catalog_Order_Transaction */
/* @var $line_item['model'] Model_Catalog_Order_Item */

$transaction = $params['transaction'];
$order = $transaction->getOrder();
?>
*************************************************
<?= OSC::helper('core/setting')->get('theme/site_name') ?> ( <?= OSC::$base_url ?> )
*************************************************

Order <?= $order->getCode() ?>


--------------------------
You have received a refund
--------------------------

Total amount refunded: <?= OSC::helper('catalog/common')->formatPriceByInteger($transaction->data['amount'], 'email_with_currency') ?>


<?= $this->build('catalog/email_template/text/order/summary', ['order' => $order]) ?>


---------------------

Got questions? Don't hesitate to <a href="<?= OSC::helper('frontend/template')->getContactUrl() . '?open_chat=1' ?>">chat with us.</a>