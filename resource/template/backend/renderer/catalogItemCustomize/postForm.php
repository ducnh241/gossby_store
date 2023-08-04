<?php
/* @var $this Helper_Backend_Template */

$this->push('[backend/template]catalogItemCustomize/builder.js', 'js')
        ->push('[backend/template]catalogItemCustomize/builder.scss', 'css')
        ->addComponent('colorPicker');
?>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm product-post-frm p25">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
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
            <div class="block mt10">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <input type="hidden" name="config" value="<?= $this->safeString(OSC::encode($params['model']->data['config'])) ?>" />
                            <div class="customize-item-builder" style="border: 1px solid #171515; border-radius: 3px; overflow: hidden;" data-upload-url="<?= $this->getUrl('*/*/upload') ?>" data-insert-cb="customizeItemBuilderInit"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>