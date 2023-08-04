<?php
/* @var $this OSC_Template */
/* @var $cart Model_Catalog_Cart */
/* @var $line_item Model_Catalog_Cart_Item */
/* @var $discount_code Model_Catalog_Discount_Code */

$cart = $params['cart'];
$discount_code = (isset($params['discount_code']) && ($params['discount_code'] instanceof Model_Catalog_Discount_Code)) ? $params['discount_code'] : false;
$cart->calculateDiscount();
$params['css_attributes']['order-line-item'] = 'border: 1px solid #DDDDDD !important; margin-bottom: 30px !important;border-radius: 8px;width:100%;';

?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['message'] ?>text-align: center !important">
                <?= $params['message'] ?>
            </div>
        </td>
    </tr>
</table>
<?php if ($discount_code) : ?><?= $this->build('catalog/email_template/html/discount_code', array_merge($params, ['apply_url' => OSC::helper('postOffice/email')->getClickUrl($cart->getRecoveryUrl($discount_code))])) ?><?php endif; ?>
<table style="<?= $params['css_attributes']['table'] ?>margin-top: 30px !important;">    
    <tr>
        <td>
            <?php foreach ($cart->getLineItems() as $line_item) : ?>
                <?php $personalized_idx = OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($line_item->getOrderItemMeta()->data['custom_data']); ?>
                <table style="<?= $params['css_attributes']['table'] ?><?= $params['css_attributes']['order-line-item'] ?>">
                    <tr>
                        <td style="padding: 20px !important">
                            <table style="<?= $params['css_attributes']['table'] ?>">
                                <tr>
                                    <td>
                                        <img src="<?= OSC::helper('core/image')->imageOptimize($line_item->getVariant()->getImageUrl(), 200, 200, true) ?>" style="display: block !important; width: 100% !important;border: 1px solid #F2F2F2;">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="margin-top: 26px!important;"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top">
                                        <div style="<?= $params['css_attributes']['order-line-item title'] ?>"><?= $line_item->getProduct()->data['title'] ?></div>
                                        <?php if ($line_item->getVariant()->getTitle()) : ?><div style="<?= $params['css_attributes']['order-line-item sub-info'] ?>"><?= $line_item->getVariant()->getTitle() ?></div><?php endif; ?>
                                        <?php if ($line_item->getProduct()->data['sku']) : ?><div style="<?= $params['css_attributes']['order-line-item sub-info'] ?>">SKU: <?= $line_item->getProduct()->data['sku'] ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background: #F6F6F6 !important; padding: 20px !important; color: #282364 !important;" align="right">                                
                            <?= $line_item->data['quantity'] ?><?= $personalized_idx !== null ? ' pcs' : '' ?> &nbsp; x &nbsp; <span style="color: #282364 !important; font-weight: bold !important"><?= OSC::helper('catalog/common')->formatPriceByInteger($line_item->data['price'], 'email_with_currency') ?></span>
                        </td>
                    </tr>    
                </table>
            <?php endforeach; ?>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important;">    
    <tr>
        <td style="font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 20px 0 5px 0 !important; color: #282364 !important;">Subtotal:</td>
        <td style="font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 20px 0 5px 0 !important; color: #282364 !important;" align="right"><?= OSC::helper('catalog/common')->formatPriceByInteger(OSC::helper('catalog/cart')->getSubtotalWithoutDiscountOfCart($cart), 'email_with_currency') ?></td>
    </tr>
    <?php if ($cart->getShippingPrice()): ?>
        <tr>
            <td style="font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important; color: #282364 !important;">Shipping:</td>
            <td style="font-family: arial, -apple-system !important; font-size: 0.875rem !important; padding: 5px 0 !important; color: #282364 !important;" align="right">
                <?= OSC::helper('catalog/common')->formatPriceByInteger($cart->getShippingPrice(), 'email_with_currency') ?>
            </td>
        </tr>
    <?php endif; ?>
</table>
<table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: dashed !important; margin: 15px 0 !important">   
    <tr>
        <td style="font-family: arial, -apple-system !important; font-size: 1.125rem !important; color: #282364 !important; font-weight: bold !important; padding: 20px 0 5px 0 !important;">Total:</td>
        <td style="font-family: arial, -apple-system !important; font-size: 1.125rem !important; color: #282364 !important; font-weight: bold !important; padding: 20px 0 5px 0 !important;" align="right"><?= OSC::helper('catalog/common')->formatPriceByInteger($cart->getTotalPriceWithoutDiscount(), 'email_with_currency') ?></td>
    </tr>
</table>
<table class="body" style="<?= $params['css_attributes']['table'] ?>max-width: 200px !important; margin-top: 30px !important; margin-bottom: 30px !important">
    <tr>
        <td align="center" valign="center">
            <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                <tr>
                    <td style="<?= $params['css_attributes']['button-cell-green'] ?>" align="center"><a href="<?= OSC::helper('postOffice/email')->getClickUrl($cart->getRecoveryUrl($discount_code)) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>">Complete Your Order</a></td>
                </tr>
            </table>
        </td>
    </tr>
</table>






