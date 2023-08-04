<?php
/* @var $this Helper_Backend_Template */
/* @var $params['model'] Model_Catalog_Discount_Code */

$this
    ->push('catalog/upload_image.js', 'js')
    ->push('catalog/review.scss', 'css');
?>
<form action="" method="post" class="post-frm p25" style="width: 950px">
    <div class="post-frm-grid catalog-review-frm">
        <div class="post-frm-grid__main-col">
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="image-uploader btn btn-primary pl20 pr20" data-insert-cb="initImgUploader" data-process-url="<?= $this->getUrl('catalog/backend_image/upload') ?>"></div>
                        </div>
                        <div class="frm-heading__action">
                            <div class="btn btn-secondary" onclick="copyImageToClipboard()">Copy images</div>
                        </div>
                    </div>
                    <div class="review-images"></div>
                </div>
            </div>
        </div>
    </div>
</form>