<?php
/* @var $this Helper_Backend_Template */
?>

<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 10px; text-align: left">Group name</th>
                <th style="width: 10px; text-align: left">Title</th>
                <th style="width: 200px; text-align: left">Unique Key</th>
                <th style="width: 200px; text-align: left">Date Added</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['collection'] as $model) : ?>

                <tr>
                    <td style="text-align: center"><?php echo $model->getId(); ?></td>
                    <td style="text-align: left"><?php echo $model->data['group_name']; ?></td>
                    <td style="text-align: left"><?php echo $model->data['title']; ?></td>
                    <td style="text-align: left"><?php echo $model->data['ukey']; ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $model->data['added_timestamp']) ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon"
                           href="<?php echo $this->getUrl('*/*/post', array('id' => $model->getId())); ?>">
                            <?php if (OSC::controller()->checkPermission('catalog/product_config/product_type_description/map/edit', false)): ?>
                                <?= $this->getIcon('pencil') ?>
                            <?php else: ?>
                                <?= $this->getIcon('eye-regular') ?>
                            <?php endif; ?>
                        </a>
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
                No Product Description added yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>