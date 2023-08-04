<?php
/* @var $this Helper_Backend_Template */

$this->push('catalog/setting_type/shipping/weight_rate.js', 'js');

$countries = OSC::helper('core/country')->getCountries();

$countries = array_merge(['*' => 'All countries'], $countries);

$provinces = null;

foreach (OSC::helper('core/country')->getProvinces() as $country_code => $group_province) {
    $counter ++;

    $provinces[$country_code] = [];

    foreach ($group_province as $province_code => $province_title) {
        $provinces[$country_code][] = ['id' => $province_code, 'title' => $province_title];
    }

}

?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
    <div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="catalogInitSettingWeightRateTable">
        <?= $this->getJSONTag(['data' => is_array($params['value']) ? $params['value'] : [], 'countries' => $countries ,'provinces'=> $provinces], 'weight_rate_table') ?>
    </div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>