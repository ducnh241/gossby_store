<?php
/* @var $this Helper_Backend_Template */
$this->push('catalog/meta-seo-image.css', 'css');
$this->push([
    'catalog/export_import_seo.js',
    'catalog/catalog_seo_meta.js',
    'quickEditSEO/quick_edit_catalog.js',
    'core/cron.js'
],'js')
?>
<div class="block m25">
    <div class="header">
        <div class="flex--grow">
            <?php if ($this->checkPermission('catalog/super|catalog/collection/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create New Collection</a>
            <?php endif; ?>
            <?php if ($this->checkPermission('catalog/collection/importDataSEO') && OSC::isPrimaryStore()) : ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductImportDataSEOBtn" data-import-url="<?= $this->getUrl('*/*/importDataSEO') ?>"  data-export-type = "collection" data-importprocess-url="<?= $this->getUrl('*/*/importProcessDataSEO') ?>">Import SEO Data</div>
            <?php endif; ?>
            <?php if ($this->checkPermission('catalog/collection/exportDataSEO') && OSC::isPrimaryStore()) : ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCatalogProductExportDataSEOBtn" data-type-selected-id = "queue_id" data-export-type="collection" data-export-url="<?= $this->getUrl('*/*/exportDataSEO') ?>" >Export Data SEO</div>
            <?php endif; ?>
        </div>

        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('catalog/super|catalog/collection/delete')) : ?>
                <div class="btn btn-danger btn-small" data-insert-cb="initCoreCronBulkActionBtn"  data-link="<?= $this->getUrl('*/*/delete') ?>" data-confirm="Do you want to delete selected collections?">Delete</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid custom-style-search">
        <?= $this->build('backend/UI/search_form',
            [
                'process_url' => $this->getUrl('*/*/search'),
                'search_keywords' => $params['search_keywords'],
                'filter_config' => $params['filter_config']
            ]) ?>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='queue_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 50px; text-align: left">&nbsp;</th>
                <th style="width: 200px; text-align: left">Title</th>
                <th style="text-align: left">Product conditions</th>
                <th style="text-align: left">Allow index</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $collection Model_Catalog_Collection */ ?>
            <?php foreach ($params['collection'] as $collection) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="queue_id" value="<?= $collection->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center"><?= $collection->getId() ?></td>
                    <td style="text-align: center"><div class="product-image-preview" style="background-image: url(<?= $this->imageOptimize($collection->getImageUrl(), 300, 300, false) ?>)"></div></td>
                    <td style="text-align: left" id="title-<?= $collection->getId() ?>"><?= $collection->data['title'] ?></td>
                    <td style="text-align: left"><?= implode('<br />', $collection->getViewableConditions()); ?></td>
                    <td style="text-align: left">
                        <?php if ($collection->data['allow_index']): ?>
                            <span id="index-badge-<?= $collection->getId() ?>" class="badge badge-green">Yes</span>
                        <?php else: ?>
                            <span id="index-badge-<?= $collection->getId() ?>" class="badge badge-danger">No</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon"
                           href="<?= OSC_FRONTEND_BASE_URL . '/' . 'catalog/collection/' . $collection->getId() . '/' . $collection->data['slug'] ?>"
                           target="_blank"><?= $this->getIcon('eye-regular') ?>
                        </a>
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/collection/quick_edit')) : ?>
                            <a class="btn btn-small btn-icon" title="Quick Edit"
                               data-insert-cb="initQuickEditInfo"
                               data-type="collection"
                               data-url-post = "<?= $this->getUrl('catalog/backend_collection/QuickEditCollection')?>"
                               data-url-get = "<?= $this->getUrl( 'catalog/backend_collection/GetCollectionInfo') ?>"
                               data-id="<?= $collection->data['collection_id'] ?>"
                               data-upload-url = '<?= $this->getUrl('backend/metaImage/UploadMetaImage') ?>'
                               data-meta-image-value = '<?= $this->safeString($collection->data['meta_tags']['image']) ?>'><?= $this->getIcon('pencil-edit') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/collection/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?= $this->getUrl('*/*/post', array('id' => $collection->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/collection/delete')) : ?>
                            <?php $collection_title = addslashes($collection->data['title']); ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the collection \"{$collection_title}\"?"))  ?>', '<?= $this->getUrl('*/*/delete', array('id' => $collection->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"</div>
    <?php endif; ?>
</div>