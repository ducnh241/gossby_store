<?php

/* @var $this OSC_Template */
/* @var $discount_code Model_Catalog_Discount_Code */

$discount_code = $params['discount_code'];

if ($discount_code->data['discount_type'] == 'percent') {
    $discount_code_value = $discount_code->data['discount_value'] . '%';
} else {
    $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
}

$message = <<<EOF
<p style="{$params['css_attributes']['message p']}">Hi <strong>{$params['first_name']}</strong></p>
<p style="{$params['css_attributes']['message p']}">Look like you have some items in your cart. Hurry up before they are gone!</p>
<p style="{$params['css_attributes']['message p']}">Below is a <span style="color: #1688FA !important; font-weight: bold !important;">{$discount_code_value} OFF</span><br />that you can use if you complete this purchase today.</p>
EOF;

$params['title'] = 'Did you forget something?';
$params['message'] = $message;
?>
<?= $this->build('catalog/email_template/html/checkout/abandoned/frame', $params) ?>
