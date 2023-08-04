<?php
/* @var $this Helper_Frontend_Template */
/* @var $product Model_Catalog_Product */
/* @var $variant Model_Catalog_Product_Variant */
/* @var $_variant Model_Catalog_Product_Variant */

$product = $params['parent_params'];
$variant =  $params['variant'];
$selector_id = OSC::makeUniqid();
?>
<div class="catalog-cart-frm__opt-selector type__product-type mb10" data-insert-cb="catalogEditVariantFrmInitOptProductTypeSelector">
    <label for="<?= $selector_id ?>"><?= $params['option_config']['title'] ?></label>
    <div class="styled-select">
        <select id="<?= $selector_id ?>" data-option="<?= $params['option_key'] ?>">
            <?php foreach ($params['option_config']['values'] as $value) : ?>
                <option value="<?= $this->safeString($value) ?>"<?php if ($variant->data[$params['option_key']] == $value) : ?> selected<?php endif; ?>><?= $this->safeString($value) ?></option>
            <?php endforeach; ?>
        </select>
        <ins></ins>
    </div>
    <div class="selector-preview">
        <?php $option_data = []; ?>
        <?php foreach ($params['option_config']['values'] as $value) : ?>
            <?php
            $option_image = '';

            foreach ($product->getVariants() as $_variant) {
                if ($_variant->data[$params['option_key']] != $value || !$_variant->ableToOrder()) {
                    continue;
                }

                if (!isset($option_data[$value])) {
                    $option_data[$value] = ['price' => OSC::helper('catalog/common')->formatPriceByInteger($_variant->data['price'], 'email_without_currency'), 'image' => ''];
                }

                if ($_variant->getImageUrl()) {
                    $option_image = $this->imageOptimize($_variant->getImageUrl(), 300, 300, false);
                    $option_data[$value]['image'] = $option_image;
                    break;
                }
            }
            ?>
            <div data-value="<?= $this->safeString($value) ?>" <?php if ($variant->data[$params['option_key']] == $value) : ?>class="selected"<?php endif; ?><?php if ($option_image): ?> style="background-image: url(<?= $this->safeString($option_image) ?>)"<?php endif; ?>></div>
        <?php endforeach; ?>
    </div>
    <?= $this->getJSONTag($option_data, 'option-images') ?>
</div>