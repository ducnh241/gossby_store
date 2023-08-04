<?php
/* @var $product Model_Catalog_Product */
/* @var $order Model_Catalog_Order */
/* @var $request Model_Catalog_Product_Review_Request */
$order = $params['order'];
$discount_percent = min(100, abs(intval(OSC::helper('core/setting')->get('catalog/product_review/review_code_percentage'))));
$params['css_attributes']['order-line-item'] = 'border: 1px solid #DDDDDD !important; margin-bottom: 30px !important;border-radius: 8px;width:100%;';

?>
<table style="margin-bottom: 15px !important;<?= $params['css_attributes']['table'] ?>">
    <tr>
        <td>
            <div style="<?= $params['css_attributes']['h1'] ?>">Review</div>
            <div style="<?= $params['css_attributes']['message'] ?>text-align: center !important;">
                <p style="<?= $params['css_attributes']['message p'] ?>">Hi <strong><?= $params['customer_first_name'] ?></strong>!</p>
                <p style="<?= $params['css_attributes']['message p'] ?>">Thanks for your purchase! Order: <?= $order->data['code'] ?></p>
                <p style="<?= $params['css_attributes']['message p'] ?>">We would be grateful if you shared how things look and feel.<br />Your review helps us and the community that supports us, and it only takes a few seconds.</p>
                <?php if (Model_Catalog_Product_Review_Request::SETT_DISCOUNT_TYPE != 'none' && $discount_percent > 0): ?>
                    <p style="<?= $params['css_attributes']['message p'] ?>">When writing emails for review, if possible, please insert the product image to be eligible for a coupon <?= $discount_percent; ?>%</p>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<table style="<?= $params['css_attributes']['table'] ?>">    
    <tr>
        <td>
            <?php foreach ($params['requests'] as $request) : ?>
                <?php
                $product = $request->getProduct();
                if (!($product instanceof Model_Catalog_Product)) {
                    continue;
                }
                $variant = $product->getSelectedOrFirstAvailableVariant();
                ?>
                <table style="<?= $params['css_attributes']['table'] ?><?= $params['css_attributes']['order-line-item'] ?>; margin-bottom: 10px !important;">
                    <tr>
                        <td style="padding: 20px !important">
                            <table style="<?= $params['css_attributes']['table'] ?>">
                                <tr>
                                    <td>
                                        <img src="<?= $variant->getImageUrl() ?>" style="display: block !important; width: 100% !important;border: 1px solid #F2F2F2;">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="margin-top: 26px!important;"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top">
                                        <div style="<?= $params['css_attributes']['order-line-item title'] ?>"><?= $product->getProductTitle() ?></div>
                                        <?php if ($variant->getVariantTitle()) : ?><div style="<?= $params['css_attributes']['order-line-item sub-info'] ?>"><?= $variant->getVariantTitle() ?></div><?php endif; ?>
                                        <?php if ($variant->data['sku']) : ?><div style="<?= $params['css_attributes']['order-line-item sub-info'] ?>">SKU: <?= $variant->data['sku'] ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table class="body" style="<?= $params['css_attributes']['table'] ?>max-width: 150px !important; margin-bottom: 30px !important; margin-left: auto !important;">
                    <tr>
                        <td align="center" valign="center">
                            <table class="button main-action-cell" style="<?= $params['css_attributes']['table'] ?>">
                                <tr>
                                    <td style="<?= $params['css_attributes']['button-cell'] ?>" align="center"><a href="<?= OSC::helper('postOffice/email')->getClickUrl($request->getUrl()) ?>" style="<?= $params['css_attributes']['button-cell a'] ?>">Write Review</a></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            <?php endforeach; ?>
        </td>
    </tr>
</table>