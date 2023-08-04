<?php
$this->addComponent('datePicker', 'timePicker');
$this->push(['catalog/meta-seo-image.css', 'post/collection.scss'], 'css');
$this->push(['catalog/catalog_seo_meta.js'], 'js');
$form_data = $params['form_data'];
?>

<form action="<?php echo $this->getUrl('*/*/*', ['id' => $params['model']->getId()]); ?>" method="post"
      class="post-frm p25 page-post-frm" style="width: 1150px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block p20">
                <div class="frm-grid">
                    <div>
                        <label for="input-title">Name</label>
                        <div><input type="text" class="styled-input" name="name" id="input-title"
                                    value="<?= $params['model']->data['name']?>"/>
                        </div>
                    </div>
                </div>
                <div class="frm-grid">
                    <div>
                        <label for="input-title">Description</label>
                        <div><textarea class="styled-textarea" name="description"
                                       id="input-description"><?= $this->safeString($params['model']->data['description']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <!--   seo mata-->
            <div class="block mt15">
                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title' => 'SEO Meta Author']) ?>
                </div>
            </div>
            <!--   end seo mata-->
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20">
                    <div class="plr20 pb20">
                        <div class="frm-heading">
                            <div class="frm-heading__main">
                                <div class="frm-heading__title">Avatar</div>
                            </div>
                        </div>
                        <div data-insert-cb="initPostFrmSidebarImageUploader"
                             data-upload-url="<?= $this->getUrl('post/backend_author/uploadImage') ?>"
                             data-input="avatar" data-image="<?= $params['model']->getAvatarUrl() ?>"
                             data-value="<?= $params['model']->data['avatar'] ?>"
                        >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>"
           class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-secondary" name="continue"
                value="1">Save & Continue
        </button>
        <button type="submit"
                class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>