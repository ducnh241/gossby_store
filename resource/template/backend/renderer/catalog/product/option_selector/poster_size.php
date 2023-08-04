<?php
/* @var $this Helper_Frontend_Template */
/* @var $product Model_Catalog_Product */
/* @var $variant Model_Catalog_Product_Variant */
/* @var $_variant Model_Catalog_Product_Variant */

$product = $params['parent_params'];
$variant =  $params['variant'];
$selector_id = OSC::makeUniqid();
?>
<div class="catalog-cart-frm__opt-selector" data-insert-cb="catalogEditVariantFrmInitOptPosterSizeSelector">
    <label for="<?= $selector_id ?>">Available dimensions:</label>
    <div class="styled-select selected-product-detail-type selected-product-detail">
        <select id="<?= $selector_id ?>" data-option="<?= $params['option_key'] ?>">
            <?php foreach ($params['option_config']['values'] as $value) : ?>
                <option value="<?= $this->safeString($value) ?>"<?php if ($variant->data[$params['option_key']] == $value) : ?> selected<?php endif; ?>><?= $this->safeString($value) ?></option>
            <?php endforeach; ?>
        </select>
        <ins></ins>
    </div>
    <?php $option_data = []; ?>
    <?php foreach ($params['option_config']['values'] as $value) : ?>
        <?php
        $option_price = '';

        foreach ($product->getVariants() as $_variant) {
            if ($_variant->data[$params['option_key']] != $value || !$_variant->ableToOrder()) {
                continue;
            }

            if (!isset($option_data[$value])) {
                $option_price = OSC::helper('catalog/common')->formatPriceByInteger($_variant->data['price'], 'email_without_currency');
                break;
            }
        }

        $value_segment = preg_replace('/[^0-9\.x]/', '', strtolower($value));
        $value_segment = explode('x', $value_segment);
        $value_segment[0] = floatval($value_segment[0]);
        $value_segment[1] = floatval($value_segment[1]);

        if (preg_match('/[0-9\.]+\s*(cm|m|mm|"|inch|in)\s*x\s*[0-9\.]+\s*(cm|m|mm|"|inch|in)/i', $value, $matches)) {
            $unit = $matches[1];
        } else {
            $unit = '"';
        }

        $option_data[$value] = $value_segment[0] . $unit . 'x' . $value_segment[1] . $unit . ' Poster ' . ($value_segment[0] == $value_segment[1] ? 'Square' : ($value_segment[0] > $value_segment[1] ? 'Horizontal' : 'Vertical')) . ' - ' . $option_price;
        ?>
    <?php endforeach; ?>
    <?= $this->getJSONTag($option_data, 'option-images') ?>
</div>
