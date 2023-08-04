<?php
/* @var $this Helper_Frontend_Template */
/* @var $product Model_Catalog_Product */
/* @var $variant Model_Catalog_Product_Variant */
$template = OSC::helper('frontend/template');
$template->addComponent('static_page');

$product = $params['product'];
$query_string = $params['query_string'];
$variant = $product->getSelectedOrFirstAvailableVariant();

echo $template->importResource('css');
?>
<a href="<?= $product->getDetailUrl() . $query_string; ?>" class="product_item_thumb" title="<?= $product->getProductTitle(); ?>">
    <img
            src="<?= $template->renderDefaultImage() ?>"
            data-src="<?= $template->imageOptimize($product->getFeaturedImageUrl(), 500, 500, false); ?>"
            data-srcset="<?= $template->imageOptimize($product->getFeaturedImageUrl(), 500, 500, false); ?> 425w,
                    <?= $template->imageOptimize($product->getFeaturedImageUrl(), 1000, 1000, false); ?> 1024w"

            alt="<?= $product->getProductTitle();?>"
            class="lazy_load" style="background: url(<?= $template->renderDefaultImage(); ?>)"
    />
</a>
<div class="product_item_info">
    <h3 class="product_item_name">
        <a href="<?= $product->getDetailUrl() . $query_string; ?>" title="<?= $product->getProductTitle(); ?>"><?= $product->getProductTitle(); ?></a>
    </h3>
    <div class="product_item_type">
        <?= $product->getProductIdentifier() ?>&nbsp;
    </div>
    <?= $template->build('catalog/product/price', array('variant' => $variant)); ?>
</div>