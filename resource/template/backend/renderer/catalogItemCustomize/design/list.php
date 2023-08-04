<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_CatalogItemCustomize_Design_Collection */
/* @var $design Model_CatalogItemCustomize_Design */
$collection = $params['collection'];

$this->push('[backend/template]catalogItemCustomize/common.js', 'js')->push('[backend/template]catalogItemCustomize/common.scss', 'css');
?>
<div class="block m25">
    <div class="header-grid">
        <!-- <div class="btn btn-primary btn-small ml5" data-insert-cb="catalogItemCustomizeInitUploadDesignsBtn" data-uploads-url="<?= $this->getUrl('*/*/importFolderDesignsUpload') ?>">Uploads Design</div> -->
        <div class="btn btn-primary btn-small ml5" data-insert-cb="catalogItemCustomizeInitExportDataBtn" data-export-url="<?= $this->getUrl('*/*/designExportData') ?>" data-search="<?= $params['in_search'] ? 1 : 0 ?>">Export Data</div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/designSearch'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <div class="customize-design-list-wrap">
            <div class="customize-design-list">
                <?php foreach ($collection as $design) : ?>
                    <?= $this->build('catalogItemCustomize/design/item', ['design' => $design]) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No customize design was found to display</div>            
    <?php endif; ?>
</div>