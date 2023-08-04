<?php
/* @var $this Helper_Backend_Template */
?>
<div class="block m25">
    <div class="header">
        <div class="flex--grow">
            <?php if ($this->checkPermission('catalog/facebook_pixel/map')): ?>
                <a class="btn btn-primary btn-small ml5"
                   href="<?= $this->getUrl('*/*/mapProductType') ?>">Map pixel to product type
                </a>
            <?php endif; ?>
        </div>

        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('catalog/facebook_pixel/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>"
                   class="btn btn-primary btn-small"><?= $this->getIcon('plus', ['class' => 'mr5']) ?>
                    Create Pixel</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">
                    <div class="styled-checkbox">
                        <input type="checkbox" data-insert-cb="initCheckboxSelectAll"
                               data-checkbox-selector="input[name='id']"/>
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                </th>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 200px; text-align: left">Title</th>
                <th style="text-align: left">Pixel ID</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $collection Model_Catalog_Collection */ ?>
            <?php foreach ($params['collection'] as $collection) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="id" value="<?= $collection->getId() ?>"/>
                            <ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center">#<?= $collection->getId() ?></td>
                    <td style="text-align: left"
                        id="title-<?= $collection->getId() ?>"><?= $collection->data['title'] ?></td>
                    <td style="text-align: left"><?= $collection->data['pixel_id'] ?></td>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('catalog/facebook_pixel/edit')) : ?>
                            <a class="btn btn-small btn-light btn-shadow btn-icon"
                               href="<?= $this->getUrl('*/*/post', ['id' => $collection->getId()]); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/facebook_pixel/delete')) : ?>
                            <?php $collection_title = addslashes($collection->data['title']); ?>
                            <a class="btn btn-small btn-light btn-shadow btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the pixel \"{$collection_title}\"?")) ?>', '<?= $this->getUrl('*/*/delete', ['id' => $collection->getId()]) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No collection was found to display</div>
    <?php endif; ?>
</div>