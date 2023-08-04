<?php $this->push('core/setting.js', 'js'); ?>
<label class="label-wrap--checker">
    <div data-insert-cb="initSettingType__Color"><input type="hidden" name="config[<?= $params['key'] ?>]" value="<?= $this->safeString($params['value']) ?>" /></div>
    <span class="ml5 bold"><?= $params['title'] ?></span>
</label>