<?php
/* @var $this Helper_Backend_Template */

$this->addComponent('select2');
$this->addComponent('location_group');
$this->push(['catalog/setting_type/shipping/delivery_time.js'], 'js');

$product_types = OSC::model('catalog/productType')->getCollection()->load();

foreach($product_types as $product_type) {
    $_product_types[$product_type->getId()] = $product_type->data['title'];
}
$data = is_array($params['value']) ? $params['value'] : [];

foreach ($data as $delivery_time_setting) {
    $delivery_time_settings[$delivery_time_setting['location_data']]['location_data'] = $delivery_time_setting['location_data'];
    $delivery_time_settings[$delivery_time_setting['location_data']]['delivery_configs'][] = [
        'product_types' => $delivery_time_setting['product_types'],
        'processing' => $delivery_time_setting['processing'],
        'estimate' => $delivery_time_setting['estimate']
    ];
}

?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="catalogInitSettingDeliveryTime">
    <div class="btn btn-primary mt10" id="add_group_delivery_time"><?= $this->getIcon('icon-plus', ['class' => 'mr5']) ?>Add New Group</div>
    <?= $this->getJSONTag([
        'delivery_time_settings' => $delivery_time_settings,
        'product_types' => $_product_types
    ],'quantity-rate-table') ?>
</div>
<?php if ($params['desc']): ?>
    <div class="input-desc">
        <?= $params['desc'] ?>
    </div>
<?php endif; ?>
