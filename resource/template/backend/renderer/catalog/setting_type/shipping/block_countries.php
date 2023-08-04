<?php
/* @var $this Helper_Backend_Template */
$countries = OSC::helper('core/country')->getCountries();
$block_countries = OSC::helper('core/setting')->get('shipping/block_countries');
$this->addComponent('select2');
$this->push(<<<EOF
function hiddenNotif() {
    setTimeout(function(){ 
        $('#notif_switch').text('') 
    }, 3000);
}
$('.multiple-selection-countries').select2({
    theme: 'default select2-container--custom'
});
$('.multiple-selection-countries').on("select2:select", function (e) {
    var data = e.params.data;
    $('#notif_switch').text('Added: ' + data.text);
    hiddenNotif();     
});
$('.multiple-selection-countries').on("select2:unselect", function (e) {
    var remove = e.params.data;
    $('#notif_switch').text('Removed: ' + remove.text) 
    hiddenNotif(); 
});
EOF
    , 'js_code');
?>

<div>
    <strong>Select name of country</strong>
    <select class="multiple-selection-countries styled-input" multiple="multiple" id="input-country-code" name="config[shipping/block_countries][]" >
        <?php foreach ($countries as $country_code => $country) : ?>
            <option value="<?= $country ?>" <?= in_array($country, $block_countries) ? 'selected' : '' ?> >
                <?= $country ?>
            </option>
        <?php endforeach; ?>
        <?= $this->getJSONTag($country, 'list_country'); ?>
    </select>
    <p id="notif_switch"></p>
</div>
