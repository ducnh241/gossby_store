<?php
/* @var $this Helper_Frontend_Template */
/* @var $product Model_Catalog_Product */
/* @var $variant Model_Catalog_Product_Variant */

$product = $params['parent_params'];
$variant =  $params['variant'];
$selector_id = OSC::makeUniqid();
?>
<div class="catalog-cart-frm__opt-selector type__clothing-size" data-insert-cb="catalogEditVariantFrmInitOptClothingSizeSelector" >
    <label for="<?= $selector_id ?>">
        <?= $params['option_config']['title'] ?>
    </label>
    <div class="styled-select selected-product-detail-type selected-product-detail">
        <select id="<?= $selector_id ?>"  data-option="<?= $params['option_key'] ?>">
            <?php foreach ($params['option_config']['values'] as $value) : ?>
                <option value="<?= $this->safeString($value) ?>"<?php if ($variant->data[$params['option_key']] == $value) : ?> selected<?php endif; ?>><?= $this->safeString($value) ?></option>
            <?php endforeach; ?>
        </select>
        <ins></ins>
    </div>

</div>