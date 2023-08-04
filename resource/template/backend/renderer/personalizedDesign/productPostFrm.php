<?php
/* @var $this Helper_Backend_Template */

$this->push('[backend/template]personalizedDesign/common.js', 'js')->push('[backend/template]personalizedDesign/common.scss', 'css');
?>

<div class="block block-grey mt15">
    <div class="plr20 pb20">
        <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Personalized design</div></div></div>
        <div class="frm-grid">
            <div>
                <div>
                    <div data-insert-cb="personalizedDesignInitBrowser"<?php if ($params['design_item'] !== null): ?> data-item="<?= $this->safeString(OSC::encode($params['design_item'])) ?>"<?php endif; ?> class="item-browser small" data-browse-url="<?= $this->getUrl('personalizedDesign/backend/browse') ?>">
                        <ins><?= $this->getIcon('search') ?></ins>
                        <input type="text" placeholder="Search for personalized design" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>