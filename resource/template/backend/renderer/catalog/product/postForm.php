<?php
/* @var $this Helper_Backend_Template */
?>
<?php

$this->addComponent('itemBrowser', 'daterangepicker', 'autoCompletePopover');
$this->push('catalog/meta-seo-image.css', 'css');
$this->push(<<<EOF
var OSC_COUNTRY_BROWSE_URL = '{$this->getUrl('core/common/browseCountry')}';
var OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browse', ['variant' => 1])}';
var OSC_CATALOG_VARIANT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browseVariant')}';
var OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_collection/browse')}';
var OSC_CATALOG_CUSTOMER_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browse')}';
var OSC_CATALOG_CUSTOMER_GROUP_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browseGroup')}';
EOF
                , 'js_code');

$this->push([
    'components/video/video-uploader.scss',
    'common/select2.min.css',
    'addon/inject.scss',
    'filter/tag.scss',
], 'css');

$this->push([
    'components/video/video-uploader.js',
    'common/select2.min.js',
    'catalog/product.js',
    'catalog/semitest_shipping_price.js',
    'catalog/catalog_seo_meta.js',
    'addon/inject.js'
], 'js');

$default_variant = $params['model']->getVariants()->getItem();
?>
<form autocomplete="off" action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm product-post-frm p25" style="width: 1200px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div><label for="input-title">Title *</label>
                            <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']) ?>" /></div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-vendor" class="required">Topic</label>
                            <div>
                                <div class="styled-autocomplete-popover" data-insert-cb="initAutoCompletePopover" data-autocompletepopover-config="<?= $this->safeString(OSC::encode(['source_url' => $this->getUrl('catalog/backend_campaign/getProductTopic')])) ?>">
                                    <input type="text" name="topic" id="input-vendor" value="<?= $this->safeString($params['model']->data['topic']) ?>" required>
                                    <ins data-autocomplete-popover-toggler="1"></ins>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-description">Description</label>
                            <div><textarea name="description" id="input-description" data-insert-cb="initSimpleEditor" style="display: none"><?= $this->safeString($params['model']->data['description']) ?></textarea></div>
                        </div>
                    </div>
                    <div class="frm-separate e20"></div>
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Images</div></div>
                        <div class="frm-heading__action"><div class="image-uploader" data-insert-cb="initProductImgUploader" data-process-url="<?= $this->getUrl('*/*/imageUpload', array('id' => $params['model']->getId())) ?>"></div></div>
                    </div>
                    <?php
                        $data_images = $params['model']->getImages()->toArray();
                        $data_request = [];
                        if (count($params['data_request_images']) > 0) {
                            foreach ($params['data_request_images'] as $tmp_name => $data) {
                                if (intval($tmp_name) > 0) {
                                    continue;
                                }
                                $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_name);
                                if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                                    continue;
                                }
                                $data_request[] = [
                                    'id' => $tmp_name,
                                    'url' => OSC::core('aws_s3')->getObjectUrl($tmp_image_path_s3)
                                ];
                            }
                        }
                    ?>
                    <div class="product-images" data-insert-cb="productPostFrm__initImages" data-request="<?= $this->safeString(OSC::encode($data_request)) ?>" data-images="<?= $this->safeString(OSC::encode($data_images)) ?>"></div>
                </div>
            </div>

            <div class="block mt15">
                <div class="pl20 pb20 pr20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Video Uploader</div></div>
                    </div>
                    <div class="video-uploader" data-insert-cb="initVideoUploader" data-max-size="<?= $params['max_video_size'] ?>" data-videos="<?= $this->safeString(OSC::encode($params['videos'])) ?>" data-process-url='/catalog/backend_campaign/uploadMockupCustomer'></div>
                </div>
            </div>

            <div class="block mt15">
                <div class="pl20 pb20 pr20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Options *</div></div>
                        <div class="frm-heading__action">
                            <div class="option-add-btn frm-heading__btn ml10" data-insert-cb="initProductAddOptionBtnV1">Add new option</div>
                        </div>
                    </div>
                    <div class="product-option-list" data-desc="Add options to the product, like different sizes or colors." data-insert-cb="productPostFrm__initOptionsV1" data-types="<?= $this->safeString(OSC::encode($params['option_types'])) ?>" data-options="<?= $this->safeString(OSC::encode($params['model']->getOrderedOptions())) ?>"></div>
                </div>
            </div>

            <div class="block mt15" style="display: none">
                <div class="pl20 pb20 pr20">
                    <div class="frm-heading">
                        <div class="frm-heading__main">
                            <div class="frm-heading__title">Variants <span id="make_variant_btn" class="option-add-btn frm-heading__btn btn-make-variant" data-insert-cb="initProductMakeVariantBtnV1">Make variants</span></div>

                        </div>
                        <div class="frm-heading__action">
                            <div id="bulk_edit_variant" class="option-add-btn frm-heading__btn btn-edit-variant" data-insert-cb="initBulkEditVariantV1">Edit</div>
                            <div id="bulk_delete_variant" class="option-add-btn frm-heading__btn ml10 btn-delete-variant" data-insert-cb="initBulkDeleteVariantV1">Delete</div>
                        </div>
                    </div>
                    <?php
                        $data_request_variants = [];
                        foreach ($params['data_request_variants'] as $key => $data) {
                            $data['meta_data'] = is_array($data['meta_data']) ? $data['meta_data'] : [];
                            $data['meta_data']['semitest_config'] = ['shipping_price' => $data['shipping_price'], 'shipping_plus_price' => $data['shipping_plus_price']];
                            $data_request_variants[$key] = $data;
                        }

                    ?>
                    <div class="product-variant-list e20"
                         data-desc="Add variants if this product comes in multiple versions, like different sizes or colors."
                         data-insert-cb="productPostFrm__initVariantsV1"
                         data-request="<?= $this->safeString(OSC::encode($data_request_variants)) ?>"
                         data-variants="<?= $this->safeString(OSC::encode($params['data_variants'])) ?>">
                    </div>
                </div>
            </div>

            <div class="block mt15" data-insert-cb="initAddonForCampaign">
                <div class="plr20 pb20">
                    <div class="frm-heading mb0">
                        <div class="frm-heading__title mr15">Manual Add-on Service</div>
                        <div class="addon-service-manual w-100p mt15">
                            <div class="addon-service-allow" style="display: block;">
                                <div class="addon-service-enable">
                                    <input
                                            type="checkbox"
                                            name="addon_service_enable"
                                            data-insert-cb="initSwitcher"
                                        <?php if ($params['model']->data['addon_service_data']['enable']): ?>
                                            checked
                                        <?php endif; ?>
                                    />
                                    <label class="label-inline ml10">On/Off Manual Addon Service</label>
                                </div>
                            </div>
                            <button class="btn addon-service-open-popup" type="button" style="margin-left: auto;">Add new service</button>
                        </div>
                    </div>
                    <input type="hidden" class="addon-service-data" name="addon_service_data" value="<?= $this->safeString(OSC::encode($params['addon_services'])) ?>" />
                    <div class="addon-service-table">
                    </div>
                    <script type="text/template" id="addon-service-template">
                        <div class="addon-service">
                            <div class="addon-service-header">
                                <div class="addon-service-label">Service 01</div>
                                <button class="ml-auto addon-service-delete" type="button">Delete</button>
                            </div>
                            <div class="addon-service-group">
                                <label class="title">Add Service</label>
                                <select class="styled-input addon-service-id" required>
                                    <option></option>
                                    <?php foreach ($params['addon_list'] as $addon): ?>
                                        <option
                                                value="<?= $addon['id'] ?>"
                                                data-title="<?= $addon['title'] ?>"
                                                data-type="<?= $addon['type'] ?>"
                                                data-product-type-id="<?= $addon['product_type_id'] ?>"
                                            <?php if ($addon['disabled']): ?>
                                                data-disabled
                                            <?php endif; ?>
                                        ><?= $addon['title'] ?> (ID: <?= $addon['id'] ?><?php if ($addon['disabled']): ?> - not available<?php endif; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="addon-service-group">
                                <label class="title">Set Test Date</label>
                                <div data-insert-cb="initAddonDateRange" data-drops="up">
                                    <input type="text" class="styled-input addon-service-daterange" required>
                                </div>
                            </div>
                        </div>
                    </script>
                </div>
            </div>

            <div class="block mt15">
                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title' => 'SEO Meta Product']) ?>
                </div>
            </div>

            <!-- Filter form select -->
            <?= $this->build('filter/tag/form_select',
                [
                    'list_product_tags' => $params['list_product_tags'],
                    'product_tag_selected' => $params['list_product_tags_selected']
                ]
            ) ?>
        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20 mt20">
                    <div class="e20"></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-vendor">Vendor *</label>
                            <div>
                                <div class="styled-select">
                                    <select name="vendor" id="input-vendor" data-insert-cb="initVendorSelector">
                                        <?= $this->getJSONTag($params['list_member_display_live_preview'], 'list_member_display_live_preview') ?>
                                        <option value="0">Select a vendor</option>
                                        <?php foreach ($params['list_vendors'] as $vendor) : ?>
                                            <option value="<?= $vendor->data['username'] ?>" <?php if ($vendor->data['username'] == $params['model']->data['vendor']) : ?> selected="selected"<?php endif; ?>><?= strtolower($this->safeString($vendor->data['username'])) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <ins></ins>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php if ($this->checkPermission('catalog/product/semitest')):?>
                        <div class="e20 frm-line"></div>
                        <div class="frm-grid">
                            <div>
                                <div class="styled-checkbox mr5">
                                    <input type="checkbox" value="1" name="is_disable_preview" id="input-is_disable_preview"<?php if (isset($params['model']->data['meta_data']['is_disable_preview']) ? ($params['model']->data['meta_data']['is_disable_preview'] == 1) : 0) : ?> checked="checked"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins>
                                </div>
                                <label class="label-inline" for="input-is_disable_preview">Disable Preview</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($params['model']->getId() > 0 && $this->checkPermission('catalog/super|catalog/product/full|catalog/product/optimize_SEO_product') && OSC::isPrimaryStore()) : ?>
                        <div class="frm-heading">
                            <div class="frm-heading__main">
                                <div class="frm-heading__title">SEO Status</div>
                            </div>
                        </div>
                        <div class="frm-grid">
                            <div>
                                <input type="checkbox" name="seo_status" data-insert-cb="initSwitcher"<?php if ($params['model']->data['seo_status'] == 1)  : ?> checked="checked"<?php endif; ?> /><label class="label-inline ml10">Optimizing product SEO</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="e20 frm-line"></div>
                    <div class="frm-grid">
                        <div 
                            id='show_product_detail_type_container'
                            <?php if ( !$params['model']->getId() || !in_array($params['model']->data['vendor'], array_column($params['list_member_display_live_preview'], 'username'))) : ?>
                                style="display: none"
                            <?php endif; ?>
                        >
                            <input 
                                type="checkbox" 
                                name="show_product_detail_type" 
                                data-insert-cb="initSwitcher"
                                <?php if ($params['model']->getId() > 0 && $params['model']->data['personalized_form_detail'] == 'live_preview') : ?>
                                    checked="checked"
                                <?php endif; ?>
                            />
                            <label class="label-inline ml10">Customize Yours in New page</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <?php if ($params['model']->getId() > 0) : ?>
            <a href="<?= $this->getUrl('*/*/*', array('id' => $params['model']->getId(), 'is_reset_cache' => 1)); ?>" class="btn btn-txt mr5">Reset cache</a>
            <a href="<?= $params['model']->getDetailUrl() ?>" class="btn btn-outline mr5" target="_blank">View product</a>
        <?php endif; ?>
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <input type="hidden" name="action" value="post_form"/>
        <button type="submit" class="btn btn-secondary" data-insert-cb="initBtnSubmitProduct" data-type="beta_product" data-action="continue">Save & Continue</button>
        <button type="submit" class="btn btn-primary" data-insert-cb="initBtnSubmitProduct" data-type="beta_product" ><?= $this->_('core.save') ?></button>
    </div>
</form>

<style>
    .select2-container--open {
        z-index: 9999999;
    }
    .select2-container--default, .select2-selection--multiple {
        border: 0 !important;
        background: transparent !important;
        width: 100% !important;
        min-height: 30px;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        width: 100% !important;
        min-height: 30px;
        text-align: left;
    }
    .select2-container .select2-search--inline {
        width: auto !important;
        min-height: 30px;
        text-align: left;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 10px !important;
    }
    .select2-dropdown {
        border: 0px;
    }
    span.select2-container.select2-container--default.select2-container--open {
        /*z-index: 10004;*/
        z-index: 99999999999;
    }
</style>
