<?php
/* @var $this Helper_Backend_Template */


$this->push('catalog/setting_type/feed_block_keyword.js', 'js')->push('catalog/feed_block_keyword.scss', 'css');

?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
    <div class="setting-feed-block-keyword" data-name="config[<?= $params['key'] ?>]" data-insert-cb="catalogInitSettingFeedBlockKeyword">
        <?= $this->getJSONTag(['data' => is_array($params['value']) ? $params['value'] : []], 'feed-block-keyword') ?>
    </div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
