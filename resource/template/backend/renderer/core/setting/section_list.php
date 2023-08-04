<?php
/* @var $this Helper_Backend_Template */

$this->push('core/setting.scss', 'css');
?>
<div class="mt25 setting-section-list">
    <?php foreach ($params['sections'] as $section) : ?>

        <?php if($this->checkPermission("settings/{$section['key']}", false)) : ?>
            <div class="section-item">
                <a href="<?= $this->getUrl('core/backend_setting/config', ['section' => $section['key']]) ?>">
                    <div class="icon"><?= $this->getIcon($section['icon']) ?></div>
                    <div class="info">
                        <div class="title"><?= $section['title'] ?></div>
                        <div class="desc"><?= $section['description'] ?></div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>