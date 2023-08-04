<?php
/* @var $this Helper_Backend_Template */
/* @var $params['model'] Model_Catalog_Discount_Code */

$this
//    ->push('catalog/upload_image.js', 'js')
    ->push('catalog/upload_print_template_image.js', 'js')
    ->push('catalog/review.scss', 'css')
    ->push('catalog/print-template-image.scss', 'css');
?>
<div id="data-url"
     data-url-post="<?= $this->getUrl('catalog/backend_PrintTemplateBeta/save') ?>"
     data-url-search="<?= $this->getUrl('catalog/backend_PrintTemplateBeta/search') ?>"
     data-url-get="<?= $this->getUrl('catalog/backend_PrintTemplateBeta/getInfo') ?>"
     data-upload-url='<?= $this->getUrl('catalog/backend_PrintTemplateBeta/uploadTmpPrintImg') ?>'
     data-delete-url='<?= $this->getUrl('catalog/backend_PrintTemplateBeta/delete') ?>'
     data-permission-edit = <?= $this->checkPermission('catalog/super|catalog/print_template_beta/list/edit') ?>>
</div>

<div class="post-frm p25" style="width: 950px">
<!--<form action="" data-insert-cb ="searchPrintTemplateBeta"  method="post" class="post-frm p25" style="width: 950px">-->
    <div class="post-frm-grid catalog-review-frm">
        <div class="post-frm-grid__main-col">
            <div class="block mt15">
                <div class="plr20 pb20">

                    <div class="form-search">
                        <?= $this->build('backend/UI/search_form_custom', [
                            'search_keywords' => '',
                            'process_url' => $this->getUrl('catalog/backend_PrintTemplateBeta/search', []),
                            'filter_config' => $params['filter_config'],
                            'filter_field' => $params['filter_field'],
                            'selected_filter_field' => [],
                            'default_search_field_key' =>  $params['default_select_field']
                        ]) ?>
                    </div>
                    <?php if($this->checkPermission('catalog/super|catalog/print_template_beta/list/add')) : ?>
                    <div class="frm-heading">
                        <div class="frm-heading__main" style="margin-left: auto !important; flex: 0 0 auto;">
                            <div class="btn btn-primary"
                                 data-insert-cb="initUploadPrintTemplateBeta"
                                 data-url-post="<?= $this->getUrl('catalog/backend_PrintTemplateBeta/save') ?>"
                                 data-url-get="<?= $this->getUrl('catalog/backend_PrintTemplateBeta/getInfo') ?>"
                                 data-id = 0
                                 data-upload-url='<?= $this->getUrl('catalog/backend_PrintTemplateBeta/uploadTmpPrintImg') ?>'
                            > Upload Print Template Beta</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="image-list-scene"
                         data-insert-cb = "initRenderPrintList"
                    ></div>
                </div>
            </div>
        </div>
    </div>
<!--</form>-->

</div>