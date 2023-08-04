<?php
/* @var $this Helper_Backend_Template */
?>

<form action="<?php echo $this->getUrl('*/*/*', ['id' => $params['model']->getId()]); ?>" method="post"
      class="post-frm p25" style="width: 750px">
    <div class="block">
        <div class="header">
            <div class="header__main-group">
                <div class="header__heading"><?= $params['form_title'] ?></div>
            </div>
        </div>
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <label for="input-title">Title *</label>
                    <div><input class="styled-input" type="text" name="title" id="input-title" required
                                value="<?= $this->safeString($params['model']->data['title']); ?>"/></div>
                </div>
            </div>

            <div class="frm-grid">
                <div>
                    <label for="input-description">Description</label>
                    <div><textarea name="description" id="input-description" data-insert-cb="initEditor"
                                   style="display: none"><?= $this->safeString($params['model']->data['description']) ?></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/index') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <?php if (OSC::controller()->checkPermission('catalog/product_config/product_type_description/edit', false) || OSC::controller()->checkPermission('catalog/product_config/product_type_description/add', false)): ?>
            <button type="submit" class="btn btn-primary "><?= $this->_('core.save') ?></button>
        <?php endif; ?>
    </div>
</form>