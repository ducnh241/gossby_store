<?php
/* @var $this Helper_Backend_Template */
$this->push('post/collection.scss', 'css');
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('post/collection/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Post Collection</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left; width: 20px;">ID</th>
                <th style="text-align: left;">Title</th>
                <th style="text-align: left;">Slug</th>
                <th style="text-align: left;">Priority</th>
                <th style="text-align: left; width: 300px">Date Added</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['collection'] as $collection) : ?>
                <tr>
                    <td style="text-align: left"><?= $collection->data['collection_id'] ?></td>
                    <td style="text-align: left"><?= $collection->data['title'] ?></td>
                    <td style="text-align: left">
                        <?php if (!empty($collection->data['meta_tags']['slug'])): ?>
                            <?= $collection->data['meta_tags']['slug'] ?>
                        <?php else: ?>
                            <?= $collection->data['slug'] ?>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: left"><?= $collection->data['priority'] ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i:s', $collection->data['added_timestamp']) ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?= $collection->getDetailUrl() ?>"
                           target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                        <?php if ($this->checkPermission('post/collection/edit')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?php echo $this->getUrl('*/*/post', array('id' => $collection->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('post/collection/delete')) : ?>
                        <?php $collection_title = addslashes($collection->data['title']); ?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the post collection \"{$collection_title}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $collection->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No post collections created yet.</div>
    <?php endif; ?>
</div>