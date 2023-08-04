<?php
/* @var $this Helper_Backend_Template */
/* @var $params['model'] Model_Catalog_Discount_Code */

$this->addComponent('datePicker', 'timePicker', 'itemSelector')
    ->push('catalog/review.js', 'js')
    ->push('catalog/review.scss', 'css')
?>
<form action="<?= $this->getUrl('*/*/*', ['id' => $params['model']->getId(), 'save' => 1]) ?>" method="post" class="post-frm p25" style="width: 950px">
    <div class="post-frm-grid catalog-review-frm">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">
                                Reply review: <a href="mailto: <?= $this->safeString($params['model']->data['customer_email']) ?>"><?= $this->safeString($params['model']->data['customer_name']) ?></a> about <a href="<?= $this->safeString($params['model']->getProductDetailUrl()) ?>"><?= $this->safeString($params['model']->getProductTitle()) ?></a>
                                <br/>
                                (<a href="<?= $this->getUrl('*/*/post', ['id' => $params['model']->getId()]) ?>">View Detail</a>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Review content</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <textarea class="styled-textarea" name="review" id="input-review"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Images</div></div>
                        <div class="frm-heading__action"><div class="image-uploader" data-insert-cb="initReviewImgUploader" data-process-url="<?= $this->getUrl('catalog/backend_review/imageUpload') ?>"></div></div>
                    </div>
                    <div class="review-images" data-insert-cb="reviewFrm__initImages" data-images="<?= $this->safeString(OSC::encode([])) ?>"></div>
                </div>
            </div>
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20 mt20">
                    <div class="frm-grid">
                        <div>
                            <div class="styled-checkbox mr5">
                                <input type="checkbox" value="1" name="allow_reply" id="input-allow_reply" checked="checked" /><ins><?= $this->getIcon('check-solid') ?></ins>
                            </div>
                            <label class="label-inline" for="input-allow_reply">Allow customer to reply</label>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-added_date">Added date</label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="added_date" id="input-added_date" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY'))) ?>" value="" data-insert-cb="initDatePicker" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
        <input type="hidden" name="parent_id" value="<?= $params['model']->getId(); ?>"/>
    </div>
</form>