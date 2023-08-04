<?php
/* @var $this Helper_Backend_Template */
?>
<?php
$this->addComponent('itemBrowser', 'autoCompletePopover');

$this->addComponent('datePicker', 'daterangepicker', 'timePicker', 'itemSelector')
        ->push(<<<EOF
var OSC_COUNTRY_BROWSE_URL = '{$this->getUrl('core/common/browseCountry')}';
var OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browse', ['variant' => 1])}';
var OSC_CATALOG_VARIANT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browseVariant')}';
var OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_collection/browse')}';
var OSC_CATALOG_CUSTOMER_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browse')}';
var OSC_CATALOG_CUSTOMER_GROUP_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_customer/browseGroup')}';
EOF
                , 'js_code');

$this->push(['common/select2.min.css', 'addon/inject.scss'], 'css');
$this->push(['common/select2.min.js', 'catalog/product.js', 'addon/inject.js'], 'js');

$default_variant = $params['model']->getVariants()->getItem();

$collections = array();

foreach ($params['model']->getCollections() as $collection) {
    $collections[] = array(
        'id' => $collection->getId(),
        'title' => $collection->data['title'],
        'auto' => $collection->data['collect_method'] == Model_Catalog_Collection::COLLECT_AUTO
    );
}
?>
<form method="post" action="<?= $this->getUrl('catalog/backend_campaign/post', ['state' => 'save', 'id' => $params['model']->getId()]) ?>" class="post-frm product-post-frm p25" style="width: 1050px">
    <input type="hidden" name="campaign_data" value="<?= $this->safeString(OSC::encode($params['campaign_data'])) ?>" />
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <label for="input-title" class="required">Quote</label>
                            <div><input type="text" class="styled-input" name="title" id="input-title" value="<?= $this->safeString($params['model']->data['title']) ?>" required="required" /></div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
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
                    <div class="frm-line e20"></div>
                    <?php if ($params['model']->getProductIdentifier()) : ?>
                        <div class="frm-grid">
                            <div>
                                <label for="input-vendor">Type</label>
                                <div style="position: relative"><?= $this->safeString($params['model']->getProductIdentifier()) ?> <span style="position: absolute; right: 0; text-transform: uppercase; color: #a5b9d8; font-size: 11px; font-weight: bold;">auto</span></div>
                            </div>
                        </div>
                        <div class="frm-line e20"></div>
                    <?php endif; ?>
                    <div class="frm-grid">
                        <div data-insert-cb="productPostFrm__initTags" data-tags="<?= $this->safeString(OSC::encode($params['model']->data['tags'])) ?>">
                            <label for="input-tags">Tags</label>
                            <div class="frm-link"><a href="#">View all tags</a></div>
                            <div>
                                <input type="text" class="styled-input" id="input-tags" data-insert-cb="initAutoCompletePopover" data-autocompletepopover-config="<?= $this->safeString(OSC::encode(['source_url' => $this->getUrl('catalog/backend_product/getProductTags'), 'select_callback' => 'productPostFrm__addTag'])) ?>" />
                            </div>
                            <div class="product-tags"></div>
                        </div>
                    </div>
                    <div class="frm-line e20"></div>
                    <div class="frm-grid">
                        <div>
                            <label>Collections</label>
                            <div>
                                <div class="item-browser small" data-insert-cb="initItemBrowser" data-browser-config="<?= $this->safeString(OSC::encode(array('focus_browse' => true, 'click_callback' => 'productPostFrm__collectionUpdate', 'item_render_callback' => 'productPostFrm__collectionCheck', 'browse_url' => $this->getUrl('catalog/backend_collection/browse', array('filter_type' => Model_Catalog_Collection::COLLECT_MANUAL))))) ?>">
                                    <ins><?= $this->getIcon('search') ?></ins>
                                    <input type="text" placeholder="Search for collections" />
                                </div>
                            </div>
                            <div class="product-collections" data-placeholder="Add this product to a collection so it’s easy to find in your store." data-insert-cb="productPostFrm__initCollections" data-collections="<?= $this->safeString(OSC::encode($collections)) ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt15">
                <div class="plr20 pb20">
                    <div class="frm-heading">
                        <div class="frm-heading__main"><div class="frm-heading__title">Description</div></div>
                    </div>
                    <div class="frm-grid">
                        <div>
                            <div><textarea name="description" id="input-description" data-insert-cb="initSimpleEditor" style="display: none"><?= $this->safeString($params['model']->data['description']) ?></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!count($params['pack_info'])): ?>
            <div class="block mt15" data-insert-cb="initAddonForCampaign">
                <div class="plr20 pb20">
                    <div class="frm-heading mb0">
                        <div class="frm-heading__title mr15">Manual Add-on Service</div>
                    </div>
                    <input type="hidden" class="addon-service-data" name="addon_service_data" value="<?= $this->safeString(OSC::encode($params['addon_services'])) ?>" />
                    <div class="mt15">
                        <div class="addon-service-manual">
                            <div class="addon-service-allow">
                                <input
                                        type="checkbox"
                                        name="addon_service_enable"
                                        data-insert-cb="initSwitcher"
                                    <?php if ($params['model']->data['addon_service_data']['enable']): ?>
                                        checked
                                    <?php endif; ?>
                                /> <label class="label-inline ml10">On/Off Manual Addon Service</label>
                            </div>
                            <button class="btn addon-service-open-popup" type="button" style="margin-left: auto;">Add new service</button>
                        </div>
                        <div class="addon-service-table">
                        </div>
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
                                    <?php foreach ($params['custom_apply_addons'] as $addon): ?>
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

                    <?php if (!empty($params['auto_apply_addons'])): ?>
                        <div class="frm-heading mb10">
                            <div class="frm-heading__title">Auto apply add-on services</div>
                        </div>
                        <div class="addon-service-auto-apply">
                            <table>
                                <thead>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Auto apply for</th>
                                <th>Active Time</th>
                                </thead>
                                <tbody>
                                <?php foreach ($params['auto_apply_addons'] as $addon): ?>
                                    <tr>
                                        <td><?= $addon['id'] ?></td>
                                        <td><?= $addon['title'] ?></td>
                                        <td><?= $addon['type'] ?></td>
                                        <td><?= $addon['apply_for'] ?></td>
                                        <td><?= $addon['date_range'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?= implode('', $params['columns']['main']) ?>
            <!-- meta seo-->
            <div class="block mt15">
                <div class="p20">
                    <?= $this->build('backend/form/meta_seo', ['model' => $params['model'], 'heading_title'=>'SEO Meta Campaign']) ?>
                </div>
            </div>
            <!-- end meta seo-->

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
                <div class="plr20 pb20">
                    <div class="frm-heading"><div class="frm-heading__main"><div class="frm-heading__title">Organization</div></div></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-vendor" class="required">Vendor</label>
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
                    <div class="e20 frm-line"></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-position_index">Set position manually</label>
                            <div>
                                <input type="text" class="styled-input" name="position_index" id="input-position_index" value="<?= $this->safeString($params['model']->data['position_index']) ?>">
                            </div>
                        </div>
                    </div>
                    <?php if ($this->getAccount()->isAdmin()) : ?>
                        <div class="frm-grid">
                            <div>
                                <div class="styled-checkbox mr5">
                                    <input type="checkbox" value="1" name="discarded" id="input-discarded"<?php if ($params['model']->data['discarded'] == 1) : ?> checked="checked"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins>
                                </div>
                                <label class="label-inline" for="input-discarded">Discard this product</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->setting('catalog/product_default/listing_admin') != 1 || $this->checkPermission('catalog/super|catalog/product/full|catalog/product/listing')) : ?>
                        <div class="frm-grid">
                            <div>
                                <div class="styled-checkbox mr5">
                                    <input type="checkbox" value="1" name="listing" id="input-listing"<?php if (isset($params['model']->data['listing']) ? ($params['model']->data['listing'] == 1) : $this->setting('catalog/product_default/listing') == 1) : ?> checked="checked"<?php endif; ?> /><ins><?= $this->getIcon('check-solid') ?></ins>
                                </div>
                                <label class="label-inline" for="input-listing">List this product</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php /*if ($this->setting('catalog/product_default/listing_admin') != 1 || $this->getAccount()->isAdmin()) : ?>
                        <div class="e20 frm-line"></div>

                        <div class="frm-grid">
                            <div>
                                <label for="input-position_index">Design price ($)</label>
                                <div>
                                    <input class="styled-input" name="buy_design_price" id="input-buy_design_price" value="<?= isset($params['model']->data['meta_data']['buy_design']['buy_design_price']) && $params['model']->data['meta_data']['buy_design']['buy_design_price'] !== '' ? $this->safeString($params['model']->data['meta_data']['buy_design']['buy_design_price'] / 100) : '' ?>" onfocusout="validatePositiveNumber(this)">
                                    <script>
                                        function validatePositiveNumber(obj) {
                                            let $this = $(obj)
                                            let value = $this.val()

                                            if (isNaN(value))
                                            {
                                                alert("Must input numbers");
                                                return false;
                                            }

                                            if (value != Math.abs(value)) {
                                                alert('Must input positive value');
                                                $this.val(Math.abs(value))
                                            }
                                        }
                                    </script>
                                </div>
                            </div>
                        </div>
                    <?php endif; */?>
                    <div class="e20 frm-line"></div>
                    <div class="frm-grid">
                        <div>
                            <label for="input-upc">Add UPC to product</label>
                            <div>
                                <input type="text" class="styled-input" name="upc" id="input-upc"  maxlength="13" value="<?= $this->safeString($params['model']->data['upc']) ?>" onfocusout="validateUPC(this)">
                                <script>
                                    function validateUPC(obj) {
                                        let $this = $(obj)
                                        let value = $this.val()

                                        if (isNaN(value))
                                        {
                                            alert("Must input numbers");
                                            return false;
                                        }
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
                    <div class="e20 frm-line"></div>
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
            <?= implode('', $params['columns']['sidebar']) ?>
        </div>
    </div>
    <div class="action-bar">

        <?php if ($params['model']->getId() > 0) : ?>
            <a href="<?= $this->getUrl('catalog/backend_product/post', array('id' => $params['model']->getId(), 'is_reset_cache' => 1)); ?>"
               class='btn btn-txt mr5'>Reset cache</a>
            <a href="<?= $params['model']->getDetailUrl() ?>" class="btn btn-outline mr5" target="_blank">View product</a>
        <?php endif; ?>
        <a href="<?= $this->getUrl('*/backend_product/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-secondary" data-insert-cb="initBtnSubmitProduct" data-type="campaign" data-action="continue" name="continue" value="1">Save & Continue</button>
        <button type="submit" class="btn btn-primary" data-insert-cb="initBtnSubmitProduct" data-type="campaign"><?= $this->_('core.save') ?></button>
    </div>
</form>
