<?php
/* @var $this Helper_Backend_Template */
?>

<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if (OSC::controller()->checkPermission('catalog/product_config/product_type_description/add', false)): ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Product Type Description</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid">
        <?= $this->build('backend/UI/search_form',
            [
                'process_url' => $this->getUrl('*/*/search'),
                'search_keywords' => $params['search_keywords'],
                'filter_config' => $params['filter_config']
            ]
        ) ?>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 400px; text-align: left">Title</th>
                <th style="text-align: left">Description</th>
                <th style="width: 200px; text-align: left">Date Added</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['collection'] as $model) : ?>
            <?php /* @var $model Model_Catalog_ProductTypeDescription */ ?>
                <tr>
                    <td style="text-align: center">#<?php echo $model->getId(); ?></td>
                    <td style="text-align: left"><?php echo $model->data['title']; ?></td>
                    <td style="text-align: left">
                        <?php echo substr(strip_tags($model->data['description']), 0, 300); ?>
                        <?= strlen($model->data['description']) > 300 ? '[...]' : '' ?>
                    </td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $model->data['added_timestamp']) ?></td>
                    <td style="text-align: right">
                        <?php if (OSC::controller()->checkPermission('catalog/product_config/product_type_description/edit', false)): ?>
                            <a class="btn btn-small btn-icon"
                               href="<?php echo $this->getUrl('*/*/post', array('id' => $model->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/product_config/product_type_description/delete')) : ?>
                            <?php if ($model->checkIsUsing()): ?>
                                <a class="btn btn-small btn-icon" disabled="disabled"
                                   href="javascript:void(0)"><?= $this->getIcon('trash-alt-regular') ?></a>
                            <?php else: ?>
                                <a class="btn btn-small btn-icon"
                                   href="javascript:$.confirmAction('<?php echo htmlentities("Do you want to delete product type description: \"{$model->data['title']}\"", ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>', '<?php echo $this->getUrl('*/*/delete', array('id' => $model->getId())); ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
                Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
                No Product Descriptions added yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>