<?php
/* @var $discount_code Model_Catalog_Discount_Code */
$recommended_items = $params['recommended_items'];
$discount_code = $params['discount_code'];

if ($discount_code->data['discount_type'] == 'percent') {
    $discount_code_value = $discount_code->data['discount_value'] . '%';
} else {
    $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
}
?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['h1'] ?>">Thanks For Your Review!</div>
            <div style="<?= $params['css_attributes']['message'] ?>text-align: center !important">
                <p style="<?= $params['css_attributes']['message p'] ?>">Hi <strong><?= $params['customer_first_name'] ?></strong></p>
                <p style="<?= $params['css_attributes']['message p'] ?>">To say thanks for review with us,<br />here's a <span style="color: #1688FA !important; font-weight: bold !important;"><?= $discount_code_value ?> OFF</span> for your next purchase.</p>
            </div>
        </td>
    </tr>
</table>
<?= $this->build('catalog/email_template/html/discount_code', $params) ?>
<table style="<?= $params['css_attributes']['table'] ?>max-width: 200px !important; margin-top: 30px !important; margin-bottom: 30px !important">
    <tr>
        <td align="center" valign="center">
            <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                <tr>
                    <td style="<?= $params['css_attributes']['button-cell'] ?>" align="center"><a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC_FRONTEND_BASE_URL) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>">Continue Shopping</a></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="padding-top: 15px !important; border-top: 1px dashed #E0E0E0 !important; text-align: left !important; margin-top: 30px !important;">
    <div style="<?= $params['css_attributes']['font'] ?>font-size: 1.125rem !important; line-height: 1.5rem !important; color: #282364 !important; font-weight: bold !important;">You might also like...</div>
    <div style="<?= $params['css_attributes']['font'] ?>line-height: 1.5rem !important; color: #282364 !important;">Check out these awesome products just waiting for you in our store.</div>
</div>
<table style="<?= $params['css_attributes']['table'] ?>margin-top: 30px !important;">
    <tr>
        <?php $counter = 0; ?>
        <?php foreach ($recommended_items as $recommended_item) : ?>
            <?php $counter ++ ?>
            <?php if ($counter > 1 && $counter % 2) : ?></tr><tr><?php endif; ?>
            <td style="width: 48% !important; text-align: center; padding-bottom: 30px !important;" align="center" valign="top">
                <div style="text-align: center;">
                    <div style="background-image: url(<?= OSC::helper('core/image')->imageOptimize($recommended_item->getFeaturedImageUrl(), 300, 300, true) ?>) !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: contain !important;">
                        <a style="outline: 0 !important; border: none !important; text-decoration: none !important; display: block !important; height: 250px;" href="<?= OSC::helper('postOffice/email')->getClickUrl($recommended_item->getDetailUrl() . '?_code=' . $discount_code->data['discount_code']) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>" target="_blank" rel="noopener noreferrer">&nbsp;</a>
                    </div>
                    <div style="margin: 10px 0 !important;">
                        <a style="text-decoration: none !important; color: #282364 !important; <?= $params['css_attributes']['font'] ?>" href="<?= OSC::helper('postOffice/email')->getClickUrl($recommended_item->getDetailUrl() . '?_code=' . $discount_code->data['discount_code']) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>" target="_blank" rel="noopener noreferrer"><?= $recommended_item->getProductTitle() ?></a>
                    </div>
                    <table style="<?= $params['css_attributes']['table'] ?>max-width: 130px !important; margin: auto !important; margin-top: 10px !important;">
                        <tr>
                            <td align="center" valign="center">
                                <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                                    <tr>
                                        <td style="<?= $params['css_attributes']['button-cell'] ?>" align="center"><a href="<?= OSC::helper('postOffice/email')->getClickUrl($recommended_item->getDetailUrl() . '?_code=' . $discount_code->data['discount_code']) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>">Shop Now</a></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <?php if ($counter % 2) : ?><td style="width: 4%">&nbsp;</td><?php endif; ?>
        <?php endforeach; ?>
    </tr>
</table>
