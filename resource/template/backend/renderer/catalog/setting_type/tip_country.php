<?php
/* @var $this Helper_Backend_Template */

$this->addComponent('select2');
$this->addComponent('location_group');

$tip_country = OSC::helper('core/setting')->get('tip/country');
$countries = [];

if ($tip_country) {
    $data_parse = array_values(OSC::helper('core/country')->getCountryCodeByLocation([$tip_country]));
    foreach ($data_parse as $country_code) {
        $country_title = OSC::helper('core/country')->getCountryTitle($country_code);
        if ($country_title) {
            $countries[] = $country_title;
        }
    }
}

?>

<div class="frm-grid">
    <div>
        <div class="font_bold">Location Available Tip</div>
        <div data-insert-cb="initSelectGroupLocation"
             data-key="<?= "config[tip/country]" ?>"
             data-value="<?= $tip_country ?>"
             data-without_provinces="1"
             data-desc_country="desc_country"
        >
        </div>
    </div>
</div>

<div class="small_title desc_country mt5"><?= implode(', ', $countries) ?></div>