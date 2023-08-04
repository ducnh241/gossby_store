<?php

/* @var $this OSC_Template */

$site_name = OSC::helper('core/setting')->get('theme/site_name');

$message = <<<EOF
<p style="{$params['css_attributes']['message p']}">Hi <strong>{$params['first_name']}</strong></p>
<p style="{$params['css_attributes']['message p']}">I am the founder of {$site_name}. I saw that you have added items to your Shopping Cart but you did not finalize the purchase. Did you find it hard to do the purchase? Or do you want any help from me? Just let me know, I am waiting for you.</p>
<p style="{$params['css_attributes']['message p']}color: #1688FA !important">Would you like to complete your purchase?</p>
EOF;

$params['title'] = 'You left items in your basket...';
$params['message'] = $message;
?>
<?= $this->build('catalog/email_template/html/checkout/abandoned/frame', $params) ?>