<?php
/* @var $discount_code Model_Catalog_Discount_Code */
/* @var $recommended_items Model_Catalog_Product_Collection */
/* @var $recommended_item Model_Catalog_Product */
$discount_code = $params['discount_code'];
$recommended_items = $params['recommended_items'];
if ($discount_code != null) {
    if ($discount_code->data['discount_type'] == 'percent') {
        $discount_code_value = $discount_code->data['discount_value'] . '%';
    } else {
        $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
    }
}
?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['h1'] ?>">Thank you for subscribing!</div>
            <?php if ($discount_code != null): ?>
                <div style="<?= $params['css_attributes']['message'] ?>text-align: center !important">
                    <p style="<?= $params['css_attributes']['message p'] ?>">Hey
                        <strong><?= $params['customer_name'] ?></strong>,
                    </p>
                    <p style="font-style: normal;font-weight: normal;font-size: 14px;line-height: 16px;text-align: center;/* #282364 */color: #282364;">
                        To say thanks for subscribe with us, here's a <span
                                style="color: #1688FA !important; font-weight: bold !important;"><?= $discount_code_value ?> OFF</span>
                        your next purchase<br/>within 2 weeks next.
                    </p>
                </div>
            <?php endif; ?>
        </td>
    </tr>
</table>
<?php if ($discount_code != null): ?>
    <?= $this->build('catalog/email_template/html/discount_code', $params) ?>
<?php endif; ?>
<table style="<?= $params['css_attributes']['table'] ?>max-width: 200px !important; margin-top: 30px !important; margin-bottom: 30px !important">
    <tr>
        <td align="center" valign="center">
            <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                <tr>
                    <td style="<?= $params['css_attributes']['button-cell'] ?>" align="center">
                        <a href="<?= OSC_FRONTEND_BASE_URL ?>" style="<?= $params['css_attributes']['button-cell a'] ?>">
                            Continue Shopping</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="padding-top: 15px !important; border-top: 1px dashed #E0E0E0 !important; text-align: left !important; margin-top: 30px !important;">
    <div style="<?= $params['css_attributes']['font'] ?>font-size: 1.125rem !important; line-height: 1.5rem !important; color: #282364 !important; font-weight: bold !important;">
        You might also like...
    </div>
    <div style="<?= $params['css_attributes']['font'] ?>line-height: 1.5rem !important; color: #282364 !important;">
        Check out these awesome products just waiting for you in our store.
    </div>
</div>
<table style="<?= $params['css_attributes']['table'] ?>margin-top: 30px !important;">
    <tr>
        <?php $counter = 0; ?>
        <?php foreach ($recommended_items

        as $recommended_item) : ?>
        <?php $counter++ ?>
        <?php if ($counter > 1 && $counter % 2) : ?></tr>
    <tr><?php endif; ?>
        <td style="width: 48% !important; text-align: center; padding-bottom: 30px !important;" align="center"
            valign="top">
            <div style="text-align: center;">
                <div style="background-image: url(<?= OSC::helper('core/image')->imageOptimize($recommended_item->getFeaturedImageUrl(), 300, 300, true) ?>) !important; background-repeat: no-repeat !important; background-position: center center !important; background-size: contain !important;">
                    <a style="outline: 0 !important; border: none !important; text-decoration: none !important; display: block !important; height: 250px;"
                       href="<?php echo $recommended_item->getDetailUrl() ?>" target="_blank" rel="noopener noreferrer">&nbsp;</a>
                </div>
                <div style="margin: 10px 0 !important;">
                    <a style="text-decoration: none !important; color: #282364 !important; <?= $params['css_attributes']['font'] ?>"
                       href="<?php echo $recommended_item->getDetailUrl() ?>" target="_blank"
                       rel="noopener noreferrer"><?= $recommended_item->data['title'] ?></a>
                </div>
                <table style="<?= $params['css_attributes']['table'] ?>max-width: 130px !important; margin: auto !important; margin-top: 10px !important;">
                    <tr>
                        <td align="center" valign="center">
                            <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                                <tr>
                                    <td style="<?= $params['css_attributes']['button-cell'] ?>" align="center">
                                        <a href="<?php echo $recommended_item->getDetailUrl() ?>"
                                           style="<?= $params['css_attributes']['button-cell a'] ?>">Shop Now</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <?php if ($counter % 2) : ?>
            <td style="width: 4%">&nbsp;</td><?php endif; ?>
        <?php endforeach; ?>
    </tr>
</table>
