<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->addComponent('itemBrowser'); ?>
<?php $this->push('navigation/common.scss', 'css')->push('navigation/common.js', 'js'); ?>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 600px">
    <div class="block">
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <label for="input-title">Title</label>
                    <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']) ?>" /></div>
                </div>
            </div>
        </div>
    </div>
    <div class="block mt15">
        <div class="plr20">
            <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Navigation items</div></div></div>
            <div class="navigation-items e20" data-browse-url="<?= $this->getUrl('navigation/backend/browse') ?>">
                <div class="item-list" data-placeholder="This menu doesn't have any items." data-insert-cb="navPoster__initItems" data-items="<?= $this->safeString(OSC::encode($params['model']->getOrderedItems())) ?>"></div>
                <div class="add-new-item-btn" data-insert-cb="navPoster__initAddItemBtn"><ins></ins>Add menu item</div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>