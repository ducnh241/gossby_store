<?php
/* @var $this Helper_Backend_Template */
?>

<form action="<?php echo $this->getUrl('*/*/*', ['id' => $params['model']->getId()]); ?>" method="post"
      class="post-frm p25" style="width: 550px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Title</label>
                            <div>
                                <input type="text" class="styled-input" name="title" required="required"
                                       id="input-title"
                                       value="<?= $this->safeString($params['model']->data['title']) ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Pixel ID</label>
                            <div>
                                <input type="text" class="styled-input" name="pixel_id" id="input-pixel-id"
                                       value="<?= $this->safeString($params['model']->data['pixel_id']) ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            <?= $this->_('core.cancel') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?= $this->_('core.save') ?>
        </button>
    </div>
</form>