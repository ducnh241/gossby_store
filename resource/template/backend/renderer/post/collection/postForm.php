<?php
/* @var $this Helper_Backend_Template */
?>
<?php $this->addComponent('datePicker', 'timePicker');
$this->push(['catalog/meta-seo-image.css', 'post/collection.scss'], 'css');
$this->push(['catalog/catalog_seo_meta.js'], 'js');
$form_data = $params['form_data'];
?>

<form action="<?php echo $this->getUrl('*/*/*', ['id' => $params['model']->getId()]); ?>" method="post"
      class="post-frm p25 page-post-frm" style="width: 750px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block p20">
                <div class="frm-grid">
                    <div>
                        <label for="input-title">Title</label>
                        <div><input type="text" class="styled-input" name="title" id="input-title"
                                    value="<?= $this->safeString($form_data['title'] ? $form_data['title'] : $params['model']->data['title']) ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block p20 mt15">
                <div class="frm-grid">
                    <div>
                        <label for="product-tab-input__priority">Priority</label>
                        <div class="styled-input-wrap">
                            <input type="number" class="styled-input" id="product-tab-input__priority"
                                   value="<?= $this->safeString($params['model']->data['priority'] ?? 0) ?>"
                                   name="priority"/>
                        </div>
                    </div>
                </div>
            </div>
            <!--   seo mata-->
            <div class="block mt15">
                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title' => 'SEO Meta Post Collection']) ?>
                </div>
            </div>
            <!--   end seo mata-->
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