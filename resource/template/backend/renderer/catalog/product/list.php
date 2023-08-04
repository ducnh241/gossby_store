<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_Catalog_Product_Collection */
$this->addComponent('itemBrowser', 'autoCompletePopover');
$this->push('catalog/catalog_seo_meta.js', 'js');
$this->push('catalog/meta-seo-image.css', 'css');
$collection = $params['collection'];
$products_fixed_price_status = OSC::helper('catalog/product')->getProductsFixedPriceStatus($collection);
$this->push(['catalog/product.js', 'quickEditSEO/quick_edit_catalog.js', 'catalog/export_import_seo.js', 'catalog/campaign.js', '[core]catalog/campaign.js'], 'js')->addComponent('uploader')->push(['catalog/campaign.scss', '[core]catalog/campaign.scss'], 'css');

?>
<?= $this->getJsonTag(OSC::helper('catalog/productType')->getProductTypeTabs(), 'product-type-tabs'); ?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/export|catalog/product/import|catalog/product/exportDataSEO|catalog/product/importDataSEO') && OSC::isPrimaryStore()) : ?>
                <div class="dropdown-btn btn btn-primary btn-small ml5">Import / Export
                    <div class="dropdown-content">
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/importDataSEO')) : ?>
                            <div class="content" data-insert-cb="initCatalogProductImportDataSEOBtn" data-import-url="<?= $this->getUrl('*/*/importDataSEO') ?>" data-importprocess-url="<?= $this->getUrl('*/*/importProcessDataSEO') ?>">Import SEO Data</div>
                        <?php endif; ?>

                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/exportDataSEO')) : ?>
                            <div class="content" data-insert-cb="initCatalogProductExportDataSEOBtn" data-export-url="<?= $this->getUrl('*/*/exportDataSEO') ?>" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Export Data SEO</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit/bulk')) : ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductBulkDiscardBtn" data-process-url="<?= $this->getUrl('*/*/bulkDiscard') ?>" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Discard</div>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductBulkActiveBtn" data-process-url="<?= $this->getUrl('*/*/bulkActive') ?>" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Active</div>
            <?php endif; ?>
            <?php if ($this->setting('catalog/product_default/listing_admin') != 1 || $this->checkPermission('catalog/super|catalog/product/full|catalog/product/listing/bulk')) : ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductBulkSetListingBtn" data-process-url="<?= $this->getUrl('*/*/bulkSetListing') ?>" data-mode="1" data-search="<?= $params['in_search'] ? 1 : 0 ?>">List</div>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductBulkSetListingBtn" data-process-url="<?= $this->getUrl('*/*/bulkSetListing') ?>" data-mode="0" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Unlist</div>
            <?php endif;?>
            <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/delete/bulk')) : ?>
                <div class="btn btn-danger btn-small ml5" data-insert-cb="initCatalogProductBulkDeleteBtn" data-process-url="<?= $this->getUrl('*/*/bulkDelete') ?>" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Delete</div>
            <?php endif; ?>
            <?php if ((($this->getAccount()->isRoot() && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore()))) : ?>
                <a class="btn btn-primary btn-small ml5" href="<?= $this->getUrl('*/*/resyncSearchIndex') ?>">Resync Search Products</a>
            <?php endif; ?>
            <?php if (($this->setting('catalog/product_default/listing_admin') != 1 || $this->getAccount()->isAdmin())) : ?>
                <div class="dropdown-btn btn btn-primary btn-small ml5">Bulk Edit
                    <div class="dropdown-content">
                        <div class="content" data-insert-cb="initCatalogProductBulkSetTagBtn" data-process-url="<?= $this->getUrl('*/*/bulkSetTag') ?>" data-mode="add_tag" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Add Tags</div>
                        <div class="content" data-insert-cb="initCatalogProductBulkSetTagBtn" data-process-url="<?= $this->getUrl('*/*/bulkSetTag') ?>" data-mode="remove_tag" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Remove Tags</div>
                        <div class="content" data-insert-cb="initCatalogProductBulkSetCollectionBtn" data-process-url="<?= $this->getUrl('*/*/bulkSetCollection') ?>" data-mode="add_collection" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Add Collections</div>
                        <div class="content" data-insert-cb="initCatalogProductBulkSetCollectionBtn" data-process-url="<?= $this->getUrl('*/*/bulkSetCollection') ?>" data-mode="remove_collection" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Remove Collections</div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($this->checkPermission('catalog/super|catalog/product/full|filter/tag/list') && OSC::isPrimaryStore()) : ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductImportTagsBtn" data-import-url="<?= $this->getUrl('*/*/importTagUpload') ?>"  data-importprocess-url="<?= $this->getUrl('*/*/importTags') ?>">Import Tags</div>
            <?php endif; ?>
            <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/add') && OSC::isPrimaryStore()) : ?>
                <div class="dropdown-btn btn btn-primary btn-small ml5">Bulk Product
                    <div class="dropdown-content">
                        <div class="content" data-insert-cb="initCatalogBulkProductBetaBtn" data-import-product="beta" data-import-url="<?= $this->getUrl('*/*/bulkProductBetaUpload') ?>" data-importprocess-url="<?= $this->getUrl('*/*/bulkProduct') ?>">Beta</div>
                        <div class="content" data-insert-cb="initCatalogBulkProductBetaBtn" data-import-product="campaign" data-import-url="<?= $this->getUrl('*/*/bulkProductCampaignUpload') ?>" data-importprocess-url="<?= $this->getUrl('*/*/bulkProduct') ?>">Campaign</div>
                        <div class="content" data-insert-cb="initExportProductTypeBtn" data-export-url="<?= $this->getUrl('*/*/exportProductType') ?>">Export Product type</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/add')) : ?>
                <div class="btn btn-primary btn-small" data-insert-cb="catalogCampaignAddNew" campaign-type="<?php if ($params['campaign_type_flag'] == 'amazon') : ?>amazon<?php else: ?>default<?php endif;?>" ><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Campaign</div>

                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Beta Product</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid">
        <?= $this->build('backend/UI/search_form_custom', [
            'process_url' => $this->getUrl('*/*/search'),
            'search_keywords' => $params['search_keywords'],
            'filter_config' => $params['filter_config'],
            'filter_field' => $params['filter_field'],
            'selected_filter_field' => $params['selected_filter_field'],
            'default_search_field_key' => $params['default_search_field_key']
        ]) ?>
    </div>

    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='product_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 50px; text-align: left">&nbsp;</th>
                <th style="text-align: left">ID</th>
                <th style="text-align: left">Product</th>
                <?php if (OSC::isPrimaryStore() || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())): ?>
                    <th style="width: 100px; text-align: left">Design Ids</th>
                <?php endif; ?>
                <th style="width: 50px; text-align: left">Sold</th>
                <th style="width: 100px; text-align: left">Type</th>
                <th style="width: 50px; text-align: left">Vendor</th>
                <th style="width: 100px; text-align: left">Date Added</th>
                <th style="width: 50px; text-align: center">&nbsp;</th>
                <th style="width: 70px; text-align: center">SEO Status</th>
                <th style="width: 270px; text-align: right"></th>
            </tr>
            <?php /* @var $product Model_Catalog_Product */ ?>
            <?php foreach ($collection as $product) : ?>
                <?php
                $variants = $product->getVariants();

//                $inventory = 'N/A';
//
//                /* @var $variant Model_Catalog_Product_Variant */
//                foreach ($variants as $variant) {
//                    if ($variant->data['track_quantity'] == 1) {
//                        if ($inventory == 'N/A') {
//                            $inventory = 0;
//                        }
//
//                        $inventory += $variant->data['quantity'];
//                    }
//                }
//
//                if ($inventory !== 'N/A') {
//                    $inventory .= ' in stock';
//
//                    if (!$variants->getItem()->isDefaultVariant()) {
//                        $inventory .= ' for ' . $variants->length() . ' variants';
//                    }
//                }

                $featured_image_url = null;
                $variant = $variants->getItem();
                if ($variant instanceof Model_Catalog_Product_Variant && $variant->getId() > 0) {
                    $featured_image_url = $variant->getImageFeaturedUrl();
                }

                if (empty($featured_image_url)) {
                    $featured_image_url = $product->getFeaturedImageUrl();
                }
                ?>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="product_id" value="<?= $product->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center">
                        <div data-insert-cb="initQuickLook" data-image="<?= $featured_image_url ?>" class="thumbnail-preview" style="background-image: url(<?= $this->imageOptimize($featured_image_url, 300, 300, false) ?>)"></div>
                    </td>
                    <td style="text-align: left"><?= $product->data['product_id']; ?></td>
                    <td style="text-align: left; word-break: break-word" id="title-<?= $product->data['product_id']; ?>"><?= $product->getProductTitle(false, false, true); ?></td>
                <td>
                    <?php foreach ($params['design_ids'][$product->getId()] as $key => $design_id) : ?>
                        <a href="<?= OSC_Controller::getUrl('personalizedDesign/backend/post', ['id' => $design_id, 'type' => 'default']) ?>"
                           target="_blank"> <?= $design_id ?> </a>
                        <?php if ($key < count($params['design_ids'][$product->getId()]) - 1): ?>
                            ,
                        <?php endif ?>
                    <?php endforeach; ?>
                </td>
                <td style="text-align: left"><?= isset($params['data_sold'][$product->data['product_id']]) ? number_format($params['data_sold'][$product->data['product_id']]) : number_format($product->data['solds']);?></td>
                <td style="text-align: left;"><?= $product->data['product_type'] ?></td>
                <td style="text-align: left"><?= $product->data['vendor'] ?></td>
                <td style="text-align: left"><?= date('d/m/Y H:i:s', $product->data['added_timestamp']) ?></td>
                <td style="text-align: center">
                    <?php if ($product->data['discarded'] == 0): ?><span class="badge badge-green mb5">Active</span><?php else: ?><span class="badge badge-red mb5">Discarded</span><?php endif; ?>
                    <?php if ($product->data['listing'] == 1): ?><span class="badge badge-green mb5">Listed</span><?php else: ?><span class="badge badge-red mb5">Unlisted</span><?php endif; ?>
                    <?php if ($products_fixed_price_status[$product->getId()]): ?>
                        <span class="badge badge-warning">Fixed Price</span>
                    <?php endif; ?>
                </td>
                    <td id="seo_status-<?= $product->data['product_id'] ?>" style="text-align: center"><?php if ($product->data['seo_status'] == 1): ?><span class="badge seo_status optimazed"> </span><?php else: ?><span class="badge seo_status unoptimazed"></span><?php endif; ?></td>
                    <td style="text-align: right">
                        <?php if ($product->isCampaignMode() && $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit')) : ?>
                            <a
                                    class="btn btn-small btn-icon"
                                    title="Add Mockups"
                                    data-insert-cb="catalogCampaignAddMockups"
                                    data-product-id="<?= $product->getId() ?>"
                            ><?= $this->getIcon('add-image') ?></a>
                        <?php endif;?>
                        <?php if (($this->checkPermission('catalog/super|catalog/product/full|catalog/product/set_sref_dest'))) : ?>
                            <a class="btn btn-small btn-icon"
                               title="Set Sref Source Dest"
                               data-insert-cb="catalogProductSetSref"
                               data-sref-source="<?= (isset($product->data['meta_data']['sref']['sref_source']) && intval($product->data['meta_data']['sref']['sref_source']) > 0) ? intval($product->data['meta_data']['sref']['sref_source']) : '' ?>"
                               data-sref-dest="<?= (isset($product->data['meta_data']['sref']['sref_dest']) && intval($product->data['meta_data']['sref']['sref_dest']) > 0) ? intval($product->data['meta_data']['sref']['sref_dest']) : '' ?>"
                               data-sref-url="<?php echo $this->getUrl('*/*/postSrefSourceDest', array('id' => $product->getId())); ?>"
                               data-product-id="<?= $product->getId() ?>"?>
                                <?= $this->getIcon('setting-marketing') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($product->isCampaignMode() && (($this->checkPermission('catalog/super|catalog/product/full|catalog/product/rerender') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore()))) : ?>
                            <a class="btn btn-small btn-icon" title="Rerender Mockup Campaign" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to rerender mockup by the campaign #{$product->getId()}?") ?>', '<?= $this->getUrl('*/backend_campaign/rerenderMockup', ['id' => $product->getId()]) ?>')"><?= $this->getIcon('redo-alt-solid') ?></a>
                        <?php endif; ?>
                        <?php if ($product->data['solds'] > 0 ) : ?>
                            <?php if ($this->checkPermission('report',false) || $this->checkPermission('srefReport',false)) : ?>
                                <a class="btn btn-small btn-icon" href="<?= $this->getUrl('srefReport/backend/productDetail', ['id' => $product->getId(), 'product_page' => 1]) ?>" target="_blank"><?= $this->getIcon('analytics') ?></a>
                            <?php endif;?>
                        <?php endif; ?>
                        <a class="btn btn-small btn-icon" href="<?= $product->getDetailUrl() ?>" target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/add')) : ?>
                            <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/duplicate', array('id' => $product->getId())); ?>"><?= $this->getIcon('clone') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/quick_edit')) : ?>
                            <a class="btn btn-small btn-icon" title="Quick Edit"
                               data-insert-cb="initQuickEditInfo"
                               data-type="product"
                               data-url-post="<?= $this->getUrl('catalog/backend_campaign/QuickEditProduct') ?>"
                               data-url-get="<?= $this->getUrl('catalog/backend_campaign/GetProductInfo') ?>"
                               data-id="<?= $product->data['product_id'] ?>"
                               data-url-topic="<?= $this->safeString(OSC::encode(['source_url' => $this->getUrl('catalog/backend_campaign/getProductTopic')])) ?>"
                               data-url-tags='<?= $this->safeString(OSC::encode(['source_url' => $this->getUrl('catalog/backend_product/GetProductSEOTags'), 'select_callback' => 'productPostFrm__addSEOTag'])) ?>'
                               data-upload-url='<?= $this->getUrl('backend/metaImage/UploadMetaImage') ?>'
                               data-meta-image-value='<?= $this->safeString($product->data['meta_tags']['image']) ?>'><?= $this->getIcon('pencil-edit') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $product->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/editPrice', ['id' => $product->getId()]); ?>"><?= $this->getIcon('tweaking') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/delete')) : ?>
                            <?php $product_title = addslashes($product->data['title']); ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the product \"{$product_title}\"?")) ?>', '<?= $this->getUrl('*/*/delete', array('id' => $product->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['page'], $params['total_item'], $params['page_size'], 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
                Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
                No products added yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>