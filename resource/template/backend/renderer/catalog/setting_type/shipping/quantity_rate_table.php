<?php
/* @var $this Helper_Backend_Template */

$this->addComponent('select2');
$this->addComponent('location_group');
$this->push(['catalog/setting_type/shipping/quantity_rate.js'], 'js');

$product_types = OSC::model('catalog/productType')->getCollection()->load();

foreach($product_types as $product_type) {
    $_product_types[$product_type->getId()] = $product_type->data['title'];
}
$data = is_array($params['value']) ? $params['value'] : [];
foreach ($data as $shipping_setting) {
    $shipping_settings[$shipping_setting['location_data']]['location_data'] = $shipping_setting['location_data'];
    $shipping_settings[$shipping_setting['location_data']]['fee_configs'][] = [
        'product_types' => $shipping_setting['product_types'],
        'shipping_configs' => $shipping_setting['shipping_configs'],
        'shipping_configs_type' => $shipping_setting['shipping_configs_type'],
        'shipping_configs_dynamic' => $shipping_setting['shipping_configs_dynamic']
    ];
}

?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="catalogInitSettingQuantityRateTable">
    <div class="btn btn-primary mt10" id="add_group_quantity"><?= $this->getIcon('icon-plus', ['class' => 'mr5']) ?>Add New Group</div>
    <?= $this->getJSONTag([
        'shipping_settings' => $shipping_settings,
        'product_types' => $_product_types,
        'shipping_configs_type' => $_product_types,
        'shipping_configs_dynamic' => $_product_types
    ],'quantity-rate-table') ?>
</div>
<?php if ($params['desc']): ?>
    <div class="input-desc">
        <?= $params['desc'] ?>
    </div>
<?php endif; ?>
