<?php
/* @var $this Helper_Backend_Template */
/* @var $this Helper_Core_Country */

$this->push([
    'catalog/setting_type/tip_maximum.js'
], 'js');

?>

<div class="title">Tip maximum</div>
<div><input type="text" name="config[<?= $params['key'] ?>]" class="styled-input" value="<?= $params['value'] ?>" data-insert-cb="catalogInitSettingTipMaximum"></div>