<?php
/* @var $this Helper_Backend_Template */
/* @var $this Helper_Core_Country */

$this->push([
    'catalog/setting_type/tip.js'
], 'js');

$tip = OSC::decode($params['value']) ?: [0,0,0];

?>

<div class="setting-table" data-name="config[<?= $params['key'] ?>]"
     data-insert-cb="catalogInitSettingTip">
    <?= $this->getJSONTag(['data' => $tip], 'tip') ?>
</div>