<?php
/* @var $this Helper_Backend_Template */

$this->push('addMetaTag/setting_type/add_meta_tag.js', 'js');
?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="catalogInitSettingAddMetaTag">
    <?= $this->getJSONTag(['data' => is_array($params['value']) ? $params['value'] : []], 'add_meta_tag_v2') ?>
</div>

