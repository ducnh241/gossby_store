<?php
/* @var $this Helper_Backend_Template */
/* @var $params['model'] Model_Catalog_Product_Review */

$this->addComponent('datePicker', 'timePicker', 'itemSelector')
        ->push('catalog/review.js', 'js')
        ->push('catalog/review.scss', 'css')
        ->push(<<<EOF
var OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browse')}';
var OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_collection/browse')}';
var OSC_CATALOG_CUSTOMER_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browse')}';
EOF
                , 'js_code');

$model = $params['model'];
?>
<form action="<?= $this->getUrl('*/*/*', ['id' => $model->getId(), 'save' => 1]) ?>" method="post" class="post-frm p25" style="width: 950px">
    <div class="post-frm-grid catalog-review-frm">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Review content</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div>
                                <textarea class="styled-textarea" name="review" id="input-review"><?= $this->safeString($model->data['review']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15" data-browse-url="<?= $this->getUrl('catalog/backend_customer/browse') ?>" data-insert-cb="catalogInitReviewCustomerFrm" data-customer="<?= $this->safeString(OSC::encode(OSC::helper('catalog/common')->loadCustomerSelectorData($model->data['customer_id']))) ?>">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Customer</div></div>
                    </div>
                    <div class="frm-grid" data-section="customer-info">
                        <div>
                            <label for="input-customer_name">Customer name</label>
                            <div class="styled-input-wrap">
                                <input type="text" class="styled-input" name="customer_name" id="input-customer_name" value="<?= $this->safeString($model->data['customer_name']) ?>" />
                            </div>
                        </div>
                        <div class="separate"></div>
                        <div>
                            <label for="input-customer_email">Customer email</label>
                            <div class="styled-input-wrap">
                                <input type="text" class="styled-input" name="customer_email" id="input-customer_email" value="<?= $this->safeString($model->data['customer_email']) ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="mt20" data-section="customer-browser"></div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Order</div></div>
                    </div>
                    <div 
                        class="mt20" 
                        data-browse-url="<?= $this->getUrl('catalog/backend_order/browse') ?>" 
                        data-insert-cb="catalogInitReviewOrderBrowser" 
                        data-order-item-id="<?= $model->data['order_item_id'] ?>"
                        data-order="<?= $this->safeString(OSC::encode(OSC::helper('catalog/common')->loadOrderSelectorData($model->data['order_id']))) ?>"
                    ></div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Order Item</div></div>
                    </div>
                    <div>
                        <div>
                            <div id='catalogInitReviewOrderItem'></div>
                            <input name='country_code' type='hidden' value="<?= $this->safeString($model->data['country_code']) ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Product</div></div>
                    </div>
                    <div class="mt20" data-browse-url="<?= $this->getUrl('catalog/backend_product/browse') ?>" data-insert-cb="catalogInitReviewProductBrowser" data-product="<?= $this->safeString(OSC::encode(OSC::helper('catalog/common')->loadProductSelectorData($model->data['product_id']))) ?>"></div>
                </div>
            </div>
			<div class="block mt15">
				<div class="plr20 pb20">
					<div class="frm-heading">
						<div class="frm-heading__main"><div class="frm-heading__title">Images</div></div>
						<div class="frm-heading__action"><div class="image-uploader btn btn-primary pl20 pr20" data-insert-cb="initReviewImgUploader" data-process-url="<?= $this->getUrl('catalog/backend_review/imageUpload') ?>"></div></div>
					</div>
					<div class="review-images" data-insert-cb="reviewFrm__initImages" data-images="<?= $this->safeString(OSC::encode($model->getImages()->toArray())) ?>"></div>
				</div>
			</div>
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20 mt20">
                    <div class="review-vote-list">
                        <?php foreach (array_reverse([1 => 'Hate it', 2 => 'Didn\'t like it', 3 => 'It\'s OK', 4 => 'Like it', 5 => 'Love it!'], true) as $vote_value => $vote_title) : ?>
                            <label>
                                <input type="radio" name="vote" value="<?= $vote_value ?>"<?php if ($vote_value == $model->data['vote_value']): ?> checked="checked"<?php endif; ?> />
                                <div class="vote-item">
                                    <span class="icon"><?php for ($i = 1; $i <= 5; $i ++) : ?><?= $this->getIcon('star' . ($i <= $vote_value ? '' : '-regular')) ?><?php endfor; ?></span><span class="label"><?= $vote_title ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>                    
                    <div class="frm-line e20"></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-added_date">Added date</label>
                            <div>
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input type="text" name="added_date" id="input-added_date" data-datepicker-config="<?= $this->safeString(OSC::encode(array('date_format' => 'DD/MM/YYYY'))) ?>" value="<?= $model->data['added_timestamp'] > 0 ? date('d/m/Y', $model->data['added_timestamp']) : '' ?>" data-insert-cb="initDatePicker" />
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
    </div>
</form>