<?php
/* @var $this Helper_Backend_Template */

$this->push([
    'common/select2.min.js',
    'catalog/setting_type/plus_minus_price.js'
], 'js');
$this->push(['common/select2.min.css'], 'css');

$product_types = OSC::helper('catalog/common')->fetchProductTypes();

$price_types = ['fixed_amount' => 'Fixed Amount', 'percent' => 'Percent'];

$countries = OSC::helper('core/country')->getCountries();

$_product_types = ['*' => 'All product types'];

foreach($product_types as $product_type) {
    $_product_types[$product_type] = $product_type;
}

$countries = array_merge(['*' => 'All countries'], $countries);
?>

<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="catalogInitSettingPlusMinusPriceTable">
    <?= $this->getJSONTag(['data' => is_array($params['value']) ? $params['value'] : [], 'product_types' => $_product_types, 'price_types' => $price_types, 'countries' => $countries], 'plus-minus-price-table') ?>
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--multiple {
        border-radius: 0 !important;
    }
</style>