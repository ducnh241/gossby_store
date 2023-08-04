<?php
/* @var $this OSC_Template */
/* @var $line_item Model_Catalog_Order_Item */

$line_item = $params['line_item'];
$personalized_idx = OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($line_item->getOrderItemMeta()->data['custom_data']);
$params['css_attributes']['order-line-item'] = 'border: 1px solid #DDDDDD !important; margin-bottom: 30px !important;border-radius: 8px;width:100%;';

?>
<table style="<?= $params['css_attributes']['table'] ?><?= $params['css_attributes']['order-line-item'] ?>">
    <tr>
        <td style="padding: 20px !important">
            <table style="<?= $params['css_attributes']['table'] ?>">
                <tr>
                    <td>
                        <img src="<?= $line_item->isCampaignMode() ? $line_item->getCampaignOrderLineItemMockupUrl() : ($line_item->getVariant() ? $line_item->getVariant()->getImageUrl() : '') ?>" style="display: block !important; width: 100% !important;border: 1px solid #F2F2F2;">
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="margin-top: 26px!important;"></div>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <div style="<?= $params['css_attributes']['order-line-item title'] ?>"><?= $line_item->data['title'] ?></div>
                        <?php if (count($line_item->data['options']) > 0) : ?><div style="<?= $params['css_attributes']['order-line-item sub-info'] ?>"><?= $line_item->getVariantOptionsText() ?></div><?php endif; ?>
                        <?php if ($line_item->data['sku']) : ?><div style="<?= $params['css_attributes']['order-line-item sub-info'] ?>">SKU: <?= $line_item->data['sku'] ?></div><?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php if (!isset($params['hide_price']) || !$params['hide_price']) : ?>
        <tr>
            <td style="color: #282364 !important; background: #F6F6F6 !important; padding: 20px !important" align="right">
                <?php if ($line_item->data['refunded_quantity'] > 0) : ?>
                    <span class="order-list__item-refunded" style="font-size: 16px !important; color: #f19753 !important;">Refunded <?= $line_item->data['refunded_quantity'] ?><?= $personalized_idx !== null ? ' pcs' : '' ?>/<?= $line_item->data['quantity'] ?><?= $personalized_idx !== null ? ' pcs' : '' ?></span>
                <?php else: ?>
                    <?= (isset($params['quantity']) && $params['quantity']) ? $params['quantity'] : $line_item->data['quantity'] ?><?= $personalized_idx !== null ? ' pcs' : '' ?> &nbsp; x &nbsp; <span style="color: #282364 !important; font-weight: bold !important"><?= OSC::helper('catalog/common')->formatPriceByInteger(intval($line_item->data['price']), 'email_with_currency') ?></span>
                    <?php if ($line_item->data['discount']) : ?>
                        <br /><?= $line_item->data['discount']['discount_code'] ?> - <?= OSC::helper('catalog/common')->formatPriceByInteger($line_item->data['discount']['discount_price'], 'email_with_currency') ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>    
    <?php else : ?>
        <tr>
            <td style="background: #F6F6F6 !important; padding: 20px !important" align="right">Shipped <?= (isset($params['quantity']) && $params['quantity']) ? $params['quantity'] : $line_item->data['quantity'] ?><?= $personalized_idx !== null ? ' pcs' : '' ?></td>
        </tr>    
    <?php endif; ?>
</table>
