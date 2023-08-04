<?php
/* @var $this OSC_Template */
/* @var $discount_code Model_Catalog_Discount_Code */

$discount_code = $params['discount_code'];

if ($discount_code->data['discount_type'] == 'percent') {
    $discount_code_value = $discount_code->data['discount_value'] . '%';
} else {
    $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
}
?>
<table style="<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td style="padding: 20px !important; text-align: center !important; background-image: url(<?= $this->getImage('catalog/email_template/discount_code_bg.png') ?>) !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: cover !important; border-radius: 10px !important;">
            <div style="<?= $params['css_attributes']['font'] ?>font-size: 1rem !important; line-height: 1.1875rem !important; color: #fff !important;">Discount</div>
            <div style="<?= $params['css_attributes']['font'] ?>font-size: 3.125rem !important; line-height: 3.8125rem !important; color: #fff !important;"><?= $discount_code_value ?> OFF</div>
            <div style="<?= $params['css_attributes']['font'] ?>font-size: 1.25rem !important; line-height: 1.5rem !important; padding: 8px !important; color: #fff !important; border: 1px dashed !important; border-radius: 20px !important; max-width: 300px !important; margin: auto !important;"><?= preg_replace('/^(.{4})(.{4})(.{4})$/', '\\1-\\2-\\3', $discount_code->data['discount_code']) ?></div>
            <div style="<?= $params['css_attributes']['font'] ?>line-height: 1.0625rem !important; color: #fff !important; margin-top: 14px !important;">Expires on <?= date('F d, Y, h:i A', $discount_code->data['deactive_timestamp']) ?></div>
        </td>
    </tr>
</table>
