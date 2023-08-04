<?php
/* @var $this OSC_Template */
/* @var $order Model_Catalog_Order */
/* @var $line_item Model_Catalog_Order_Item */

$order = $params['order'];
?>
<p>Dear <?= $order->getFirstName() ?>,</p>
<p>Thank you so much for your order on our store. Regarding your order <strong><?= $order->getCode() ?></strong>, we would like to inform that your transaction is recognized as a fraud. Please help us verify the payment by sending us the picture of the last four digits of your credit card.</p>
<p>We will capture the payment and process the order once we receive the confirmation from you.</p>
<p>Thank you so much for your cooperation!</p>