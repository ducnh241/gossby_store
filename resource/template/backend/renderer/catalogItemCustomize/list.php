<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_CatalogItemCustomize_Item_Collection */
/* @var $item Model_CatalogItemCustomize_Item */
$collection = $params['collection'];
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">&nbsp;</div>
        <div><a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add new type</a></div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='item_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="text-align: left">Title</th>
                <th style="width: 170px; text-align: left">Key</th>
                <th style="width: 170px; text-align: left">Added date</th>
                <th style="width: 150px; text-align: left">Modified date</th>
                <th style="width: 135px; text-align: right"></th>
            </tr>
            <?php foreach ($collection as $item) : ?>
                <tr>
                    <td style="text-align: center"><div class="styled-checkbox"><input type="checkbox" name="item_id" value="<?= $item->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins></div></td>
                    <td style="text-align: left"><?= $item->data['title'] ?></td>
                    <td style="text-align: left"><?= $item->getUkey() ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i:s', $item->data['added_timestamp']) ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i:s', $item->data['modified_timestamp']) ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $item->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/duplicate', array('id' => $item->getId())); ?>"><?= $this->getIcon('clone') ?></a>
                        <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the type \"{$item->data['title']}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $item->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No customize type was found to display</div>            
    <?php endif; ?>
</div>