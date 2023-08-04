<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('autoCompletePopover');
$this->addComponent('daterangepicker');
$this->addComponent('itemBrowser');
$this->push(['catalog/catalog_seo_meta.js', 'catalog/collection.js'], 'js');
$this->push('catalog/meta-seo-image.css', 'css');
$search_filter_field = $params['selected_filter_field'];
$default_select_field = OSC::cookieGet($params['default_search_field_key']);
if (empty($search_filter_field)) {
    $search_filter_field = $default_select_field;
}
?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 950px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Title</label>
                            <div><input type="text" class="styled-input" name="title" required="required" id="input-title" value="<?= $this->safeString($params['model']->data['title']) ?>" /></div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-title">Custom Title</label>
                            <div><input type="text" class="styled-input" name="custom_title" id="input-custom-title" value="<?= $this->safeString($params['model']->data['custom_title']) ?>" /></div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-description">Description (optional)</label>
                            <div><textarea name="description" id="input-description" data-insert-cb="initEditor" style="display: none"><?= $this->safeString($params['model']->data['description']) ?></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Collection type</div></div></div>
                    <div class="frm-grid">
                        <div>
                            <div class="styled-radio mr5">
                                <input type="radio" data-insert-cb="catalogCollectionPostFrm_initCollectMethodSwitch" value="<?= Model_Catalog_Collection::COLLECT_MANUAL ?>" name="collect_method" id="input-collect_method-<?= Model_Catalog_Collection::COLLECT_MANUAL ?>"<?php if ($params['model']->data['collect_method'] == Model_Catalog_Collection::COLLECT_MANUAL) : ?> checked="checked"<?php endif; ?> /><ins></ins>
                            </div>
                            <label class="label-inline" for="input-collect_method-<?= Model_Catalog_Collection::COLLECT_MANUAL ?>">Manual</label>
                        </div>
                    </div>
                    <div class="input-desc pl25 mb15">Add products to this collection one by one.</div>
                    <div class="frm-grid">
                        <div>
                            <div class="styled-radio mr5">
                                <input type="radio" data-insert-cb="catalogCollectionPostFrm_initCollectMethodSwitch" value="<?= Model_Catalog_Collection::COLLECT_AUTO ?>" name="collect_method" id="input-collect_method-<?= Model_Catalog_Collection::COLLECT_AUTO ?>"<?php if ($params['model']->data['collect_method'] != Model_Catalog_Collection::COLLECT_MANUAL) : ?> checked="checked"<?php endif; ?> /><ins></ins>
                            </div>
                            <label class="label-inline" for="input-collect_method-<?= Model_Catalog_Collection::COLLECT_AUTO ?>">Automated</label>
                        </div>
                    </div>
                    <div class="input-desc pl25 mb15">Existing and future products that match the conditions you set will automatically be added to this collection.</div>
                    <div class="frm-separate e20"></div>
                    <div id="collection-config--manual"<?php if ($params['model']->data['collect_method'] != Model_Catalog_Collection::COLLECT_MANUAL) : ?> style="display: none"<?php endif; ?>>
                        <?php if ($params['model']->getData('collect_method', true) != Model_Catalog_Collection::COLLECT_MANUAL) : ?>
                            Products will be manual add after collection created/updated
                        <?php else: ?>
                            <div class="collection-product-list" data-insert-cb="catalogCollectionPostFrm_initCollectProductManage" data-collection-id="<?= $params['model']->getId() ?>" data-remove-url="<?= $this->getUrl('catalog/backend_product/removeFromCollection') ?>" data-update-url="<?= $this->getUrl('catalog/backend_collection/updateProducts') ?>" data-add-url="<?= $this->getUrl('catalog/backend_product/addToCollection') ?>" data-browse-url="<?= $this->getUrl('catalog/backend_product/browseEs') ?>" data-source-url="<?= $this->safeString($this->getUrl('catalog/backend_collection/productList')) ?>">
                                <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Products</div></div></div>
                                <div>
                                    <div class="item-browser styled-search">
                                        <?= $this->getJSONTag($params['filter_field'], 'filter-field') ?>
                                        <button type="button" class="filter filter_field">
                                            <span id="lbl_selected_field"><?= $params['filter_field'][$search_filter_field] ?? 'Select field' ;?></span> <?= $this->getIcon('angle-down-solid') ?>
                                        </button>
                                        <input type="hidden" id="selected_field" name="filter_field" value="<?= $search_filter_field; ?>" />
                                        <input type="hidden" id="default_search_field_key" value="<?= $params['default_search_field_key']; ?>" />
                                        <input type="text" placeholder="Search by Product ID/ SKU/ Title given to products" />
                                        <div data-browser-toggler="1">Browse</div>
                                    </div>
                                </div>
                                <div class="frm-line e20"></div>
                                <div class="product-list-wrap">
                                    <div class="no-result">There are no products in this collection</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div id="collection-config--auto" data-tag-autocomplete-source-url="<?= $this->safeString($this->getUrl('catalog/backend_product/getProductTags')) ?>" data-type-autocomplete-source-url="<?= $this->safeString($this->getUrl('catalog/backend_product/getProductTypes')) ?>" data-vendor-autocomplete-source-url="<?= $this->safeString($this->getUrl('catalog/backend_product/getProductVendors')) ?>" <?php if ($params['model']->data['collect_method'] == Model_Catalog_Collection::COLLECT_MANUAL) : ?> style="display: none"<?php else : ?> data-insert-cb="catalogCollectionPostFrm_initAutoConditions" data-conditions="<?= $this->safeString(OSC::encode(is_array($params['model']->data['auto_conditions']) && isset($params['model']->data['auto_conditions']['conditions']) ? $params['model']->data['auto_conditions']['conditions'] : array())) ?>"<?php endif; ?>>
                        <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">CONDITIONS</div></div></div>
                        <div class="mb15">
                            <span>Products must match:</span>
                            <div class="styled-radio ml10">
                                <input type="radio" value="all" name="condition[matched_by]" id="input-collect_method_require-all"<?php if (!is_array($params['model']->data['auto_conditions']) || !isset($params['model']->data['auto_conditions']['matched_by']) || $params['model']->data['auto_conditions']['matched_by'] != 'any') : ?> checked="checked"<?php endif; ?> /><ins></ins>
                            </div>
                            <label class="label-inline" for="input-collect_method_require-all">All condition</label>
                            <div class="styled-radio ml10">
                                <input type="radio" value="any" name="condition[matched_by]" id="input-collect_method_require-any"<?php if (is_array($params['model']->data['auto_conditions']) && isset($params['model']->data['auto_conditions']['matched_by']) && $params['model']->data['auto_conditions']['matched_by'] == 'any') : ?> checked="checked"<?php endif; ?> /><ins></ins>
                            </div>
                            <label class="label-inline" for="input-collect_method_require-any">any condition</label>
                        </div>
                        <div id="collection-auto-conditions"></div>
                        <div class="frm-line e20"></div>
                        <div class="frm-grid">
                            <div><div class="btn btn-secondary-add btn-small" id="collection-auto-add-condition-btn" data-insert-cb="catalogCollectionPostFrm_initAddConditionBtn"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add another condition</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="p20">
                    <div class="frm-heading">
                        <span class="frm-heading__title">Collection Banner</span>                  
                        <div class="styled-radio ml15 mr5">
                            <input type="radio" value="default" name="banner[option]" id="default" <?php if (!is_array($params['model']->data['meta_tags']['banner']) || !isset($params['model']->data['meta_tags']['banner']['option']) || $params['model']->data['meta_tags']['banner']['option'] == 'default') : ?> checked="checked"<?php endif; ?> /><ins></ins>
                        </div>
                        
                        <label class="label-inline" for="default">Default</label>
                        <div class="styled-radio ml15 mr5">
                            <input type="radio" value="current" name="banner[option]" id="collection" <?php if (is_array($params['model']->data['meta_tags']['banner']) && isset($params['model']->data['meta_tags']['banner']['option']) && $params['model']->data['meta_tags']['banner']['option'] == 'current') : ?> checked="checked"<?php endif; ?> /><ins></ins>
                        </div>
                        <label class="label-inline" for="collection">Use current settings</label>
                        <div class="styled-radio ml15 mr5">
                            <input type="radio" value="off" name="banner[option]" id="off-collection" <?php if (is_array($params['model']->data['meta_tags']['banner']) && isset($params['model']->data['meta_tags']['banner']['option']) && $params['model']->data['meta_tags']['banner']['option'] == 'off') : ?> checked="checked"<?php endif; ?> /><ins></ins>
                        </div>
                        <label class="label-inline" for="off-collection">Off</label>
                    </div>                    
                    <div class="frm-grid">
                        <div>
                            <label>Collection Banner Title</label>
                            <div><input type="text" class="styled-input" name="banner[title]" value="<?= $this->safeString($params['model']->data['meta_tags']['banner']['title']); ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <label>Collection Banner Url</label>
                            <div><input type="text" class="styled-input" name="banner[url]" value="<?= $this->safeString($params['model']->data['meta_tags']['banner']['url']); ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="frm-grid">
                        <div class="row" style="display: flex">
                            <div class="col-6" style="width: 100%">
                                <label>PC collection banner image</label>
                                <div data-insert-cb="initPostFrmMetaImageUploader" data-upload-url="<?= $this->getUrl('backend/metaImage/UploadMetaImage'); ?>" data-input="banner[pc]" data-image="<?= $params['model']->data['meta_tags']['banner']['pc']  ? OSC::core('aws_s3')->getStorageUrl($params['model']->data['meta_tags']['banner']['pc']) : '' ?>" data-value="<?= $this->safeString($params['model']->data['meta_tags']['banner']['pc']); ?>">
                                </div>
                            </div>
                            <div class="col-6" style="width: 100%">
                                <label>Mobile collection banner image</label>
                                <div data-insert-cb="initPostFrmMetaImageUploader" data-upload-url="<?= $this->getUrl('backend/metaImage/UploadMetaImage'); ?>" data-input="banner[mobile]" data-image="<?= $params['model']->data['meta_tags']['banner']['mobile'] ? OSC::core('aws_s3')->getStorageUrl($params['model']->data['meta_tags']['banner']['mobile']) : '' ?>" data-value="<?= $this->safeString($params['model']->data['meta_tags']['banner']['mobile']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="block mt15">
                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title' => 'SEO Meta Collection']) ?>
                </div>
            </div>

        </div>
        <div class="post-frm-grid__sub-col">
            <div class="block block-grey">
                <div class="plr20 pb20">
                    <div class="frm-heading mb5"><div class="frm-heading__main"><div class="frm-heading__title">Collection image</div></div></div>
                    <div class="small_title mb20">Artwork must be 270 x 270 px with a minimum resolution of 72 dpi. Accepted file types are jpg and png</div>
                    <div data-insert-cb="initPostFrmSidebarImageUploader" data-upload-url="<?= $this->getUrl('catalog/backend_collection/uploadImage') ?>" data-input="image" data-image="<?= $params['model']->getImageUrl() ?>" data-value="<?= $params['model']->data['image'] ?>"></div>
                    <div class="frm-line e20"></div>
                    <div>
                        <label for="input-sort_option">Sort products by</label>
                        <div>
                            <div class="styled-select">
                                <select name="sort_option" id="input-sort_option" data-insert-cb="onChangeSortOptions">
                                    <?php foreach ($params['model']->getSortOptions() as $value => $label) : ?>
                                        <option value="<?= $value ?>"<?php if ($params['model']->data['sort_option'] == $value) : ?> selected="selected"<?php endif; ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <ins></ins>
                            </div>
                        </div>
                    </div>
                    <div style="display: none" class="mt20 date-ranger">
                        <div class="frm-grid">
                            <div class="d-flex mb10">
                                <div class="flex-grow-1">
                                    <div class="styled-radio"><input type="radio" name="date_type" value="<?= Model_Catalog_Collection::DATE_TYPE_ABSOLUTE ?>" id="input_date_type_absolute" <?= $params['model']->data['best_selling_start'] || $params['model']->data['best_selling_end'] || !$params['model']->data['relative_range'] ? 'checked="checked"' : '' ?> data-insert-cb="initDateType" /><ins></ins></div>
                                    <label class="ml5 label-inline" for="input_date_type_absolute">Absolute</label>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="styled-radio"><input type="radio" name="date_type" value="<?= Model_Catalog_Collection::DATE_TYPE_RELATIVE ?>" id="input_date_type_relative" <?= $params['model']->data['relative_range'] ? 'checked="checked"' : '' ?> data-insert-cb="initDateType" /><ins></ins></div>
                                    <label class="ml5 label-inline" for="input_date_type_relative">Relative</label>
                                </div>
                            </div>
                        </div>
                        <div style="display: none" id="date_type_absolute" data-insert-cb="initDateRanger">
                            <label for="date_range">Date range</label>
                            <div class="filter-input">
                                <div class="styled-date-time-input">
                                    <div class="date-input">
                                        <?= $this->getIcon('calendar-alt') ?>
                                        <input id="date_range" type="text" value="<?= $params['model']->getBestSellingRange(); ?>" name="date_range" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="display: none" id="date_type_relative" class="date_type_relative">
                            <input id="date_type_relative" class="styled-input d-inline" type="number" min="1" value="<?= $params['model']->data['relative_range']; ?>" name="relative_range" autocomplete="off">
                            <span>days ago</span>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div>
                        <label for="select_top">Show TOP N product by sort criteria</label>
                        <div class="input-desc mb5">(Or leave blank to show all product)</div>
                        <div>
                            <input id="select_top" type="number" min="1" class="styled-input" value="<?= $params['model']->data['top']; ?>" name="top" autocomplete="off">
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div>
                        <label for="input-sort_option">Show review by</label>
                        <div>
                            <div class="styled-select">
                                <select name="show_review_mode" id="show_review_mode">
                                    <option value="1" <?php if ($params['model']->data['show_review_mode'] == 1) : ?> selected="selected"<?php endif; ?>>Collection</option>
                                    <option value="0" <?php if ($params['model']->data['show_review_mode'] == 0) : ?> selected="selected"<?php endif; ?>>All products</option>
                                </select>
                                <ins></ins>
                            </div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div class="mb5 collection-post-form">
                        <label for="input-sort_option">Allow index</label>
                        <input type="checkbox" name="allow_index"
                               id="allow_index"
                               data-insert-cb="initSwitcher"
                            <?php if ($params['model']->data['allow_index']) : ?>
                                checked="checked"
                            <?php endif; ?>
                        />
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
