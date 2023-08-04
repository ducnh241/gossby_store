<?php
/* @var $this Helper_Backend_Template */

$this->push('core/setting.scss', 'css');
$this->push('setting/form.js', 'js');
?>
<form id="setting-form" method="post" action="<?= $this->getUrl('*/*/*', ['section' => $params['section']['key'], 'save' => 1]) ?>">
    <div class="setting-config-panel post-frm">
        <?php foreach ($params['groups'] as $group) : ?>
            <?= $this->build('core/setting/group', ['group' => $group, 'setting_types' => $params['setting_types'], 'new_setting_values' => $params['new_setting_values']]) ?>
        <?php endforeach; ?>
        <div class="action-bar">
            <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
        </div>
    </div>
</form>
