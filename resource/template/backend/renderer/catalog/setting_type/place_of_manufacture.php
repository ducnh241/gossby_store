<?php
/* @var $this Helper_Backend_Template */
/* @var $this Helper_Core_Country */

$this->push([
    'common/select2.min.js',
    'catalog/setting_type/place_of_manufacture.js'
], 'js');
$this->push(['common/select2.min.css'], 'css');

$countries = OSC::helper('core/country')->getCountries();
foreach ($countries as $country_code => $country) {
    $countries[$country_code] = ['title' => $country];
}
$place_of_manufacture_value = OSC::decode($params['value']);

?>

<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]"
     data-insert-cb="catalogInitSettingPlaceOfManufacture">
    <?= $this->getJSONTag(['data' => is_array($place_of_manufacture_value) ? $place_of_manufacture_value : [], 'countries' => $countries], 'place_of_manufacture') ?>
</div>
<?php if ($params['desc']): ?>
    <div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--multiple {
        border-radius: 0 !important;
    }
</style>