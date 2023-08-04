<?php
/* @var $this Helper_Backend_Template */
/* @var $this Helper_Core_Country */

$this->push([
    'common/select2.min.js',
    'catalog/setting_type/place_of_manufacture.js'
], 'js');
$this->push(['common/select2.min.css'], 'css');

$countries = OSC::helper('core/country')->getCountries();
$name_config = "config[" . $params['key'] . "]";
?>


<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div>
<?php endif; ?>
<div class="styled-select">
    <select name="<?= $name_config ?>" class="js-place_of_manufacture_default">
        <?php foreach ($countries as $country_code => $country): ?>
            <option value="<?= $country_code ?>" <?= ($country_code == $params['value']) ? 'selected' : '' ?>> <?= $country ?></option>
        <?php endforeach; ?>
    </select>
</div>

<script>
    $(document).ready(function () {
        $('.js-place_of_manufacture_default').select2();
    });
</script>

<style>
    .select2-container--default .select2-selection--single {
        border-radius: 0 !important;
        height: 33px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 33px !important;
    }
</style>