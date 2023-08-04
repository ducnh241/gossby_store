<?php
$this->push('catalog/catalog_seo_meta.js', 'js');
$this->push('catalog/meta-seo-image.css', 'css');

$seo_slug = $params['model']->data['slug'];
$model =  $params['model'];
$heading_title = $params['heading_title'];
?>
<div class="frm-heading">
    <div class="frm-heading__main"><div class="frm-heading__title"><?= $heading_title ? $heading_title : 'SEO Meta' ?></div></div>
</div>

<div class="frm-grid">
    <div>
        <div style="display: flex">
            <label for="input-seo-title">Meta Title</label> <span id="auto_fill_auto_slug" onclick="generateSlug()"> Auto Fill</span>
        </div>

        <div><input type="text" autocomplete="off" class="styled-input" name="seo-title" onkeyup="title_generate_slug()" id="input-seo-title" value="<?= $this->safeString($model->data['meta_tags']['title']) ? $this->safeString($model->data['meta_tags']['title']) : ''  ?>" /></div>
        <label id="warnning_character_title" style="color: #FFC107" for="input-title"></label>
    </div>
</div>
<div class="frm-grid">
    <div>
        <label for="input-seo-slug">Meta Slug</label>
        <div><input type="text" autocomplete="off" class="styled-input" name="seo_slug" id="input-seo-slug" value="<?= $seo_slug ? $seo_slug : '' ?>" /></div>
        <div><input type="hidden" id="input-id-item" value="<?= $model->getId(); ?>"> </div>
        <div><input type="hidden" id="input-id-seo_slug" value="<?= $seo_slug ? $seo_slug : '' ?>"> </div>
    </div>
</div>

<div class="frm-grid">
    <div>
        <?php if($model instanceof Model_Catalog_Product) : ?>
            <label for="input-seo-slug">Link SKU:</label>
            <span style="font-size: 12px; word-break: break-all" "> <?= $model->getDetailUrl() ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="frm-grid">
    <div>
        <label for="input-seo-description">Meta Description</label>
        <div><textarea class="styled-textarea" name="seo-description" onkeyup="description_length()" id="input-seo-description" rows="5"><?= $this->safeString($model->data['meta_tags']['description']) ? $this->safeString($model->data['meta_tags']['description']) : '' ?> </textarea></div>
        <label id="warning_character_description" style="color: #FFC107"></label>
    </div>
</div>
<div class="frm-grid">
    <div>
        <label for="input-seo-keyword">Meta Keywords</label>
        <div><textarea class="styled-textarea" name="seo-keyword" id="input-seo-keyword"><?= $this->safeString($model->data['meta_tags']['keywords']) ? $this->safeString($model->data['meta_tags']['keywords']) : '' ?></textarea></div>
    </div>
</div>
<div class="frm-grid">
    <div>
        <label for="">Meta image</label>
        <div data-insert-cb="initPostFrmMetaImageUploader" data-upload-url="<?= $this->getUrl('backend/metaImage/UploadMetaImage') ?>" data-input="seo-image" data-image="<?= $model->getMetaImageUrl() ?>" data-value="<?= $this->safeString($model->data['meta_tags']['image']) ?>"></div>
        <div class="frm-line e20"></div>
    </div>
</div>