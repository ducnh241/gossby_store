<?php
$this->addComponent('itemBrowser', 'autoCompletePopover');
$this->push('catalog/setting_type/shipping/block_country.js', 'js');
?>
<div data-insert-cb="countryPostFrm__initTags" type="list_premium"
     data-tags="<?= $this->safeString(OSC::encode(OSC::helper('core/setting')->get('catalog/campaign/fleeceBlanket_50x60/list_country_by_premium'))) ?>">
    <label for="input-tags">List country for sale premium product (Enter name of country)</label>
    <div>
        <input type="text" class="styled-input" type-name="list_premium" id="input-tags" data-insert-cb="initAutoCompletePopover"
               data-autocompletepopover-config="<?= $this->safeString(OSC::encode([
                   'source_url' => $this->getUrl('core/common/getCountriesTags'),
                   'select_callback' => 'countryPostFrm__addTag'
               ])) ?>"/>
        <input type="hidden" name="config[catalog/campaign/fleeceBlanket_50x60/list_country_by_premium][]" value="">
    </div>
    <div class="product-tags"></div>
</div>

<style>
    .item.item-add.selected{display: none}
</style>