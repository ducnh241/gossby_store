<?php
/* @var $this Helper_Backend_Template */

$this->push('[backend/template]catalogItemCustomize/common.js', 'js')->push('[backend/template]catalogItemCustomize/common.scss', 'css');
?>

<div class="block block-grey mt15">
    <div class="plr20 pb20">
        <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Customize type</div></div></div>
        <div class="frm-grid">
            <div>
                <div>
                    <div data-insert-cb="catalogItemCustomizeInitBrowser"<?php if ($params['customize_item'] !== null): ?> data-item="<?= $this->safeString(OSC::encode($params['customize_item'])) ?>"<?php endif; ?> class="item-browser small" data-browse-url="<?= $this->getUrl('catalogItemCustomize/backend/browse') ?>">
                        <ins><?= $this->getIcon('search') ?></ins>
                        <input type="text" placeholder="Search for customize" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>