<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('select2');
$this->push('catalog/setting_type/collection_beta_feed.js', 'js');

$catalog_collections = OSC::model('catalog/collection')->getCollection()->addField('title')->load()->getItems();

$_catalog_collections = [];
$_catalog_collections[] = 'Please choose one collection';
/** @var $catalog_collection Model_Catalog_Collection */
foreach($catalog_collections as $catalog_collection) {
    $_catalog_collections[$catalog_collection->getId()] = $catalog_collection->data['title'];
}

$data_setting = $params['value'];
?>

<style>
    .collection-feed-table .osc-switcher {
        width: 64px;
    }
    .collection-feed-table .osc-switcher .on:before {
        content: "Prefix";
    }
    .collection-feed-table .osc-switcher .off:before {
        content: "Suffix";
    }
    body > .select2-container {
        width: initial !important;
    }
</style>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
    <div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="initSettingCollectionBetaFeed">
        <?= $this->getJSONTag(['data' => $data_setting, 'catalog_collections' => $_catalog_collections], 'collection-beta-feed') ?>
    </div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
