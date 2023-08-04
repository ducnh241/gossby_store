<?php
/* @var $this Helper_Backend_Template */
$this->push('page/common.scss', 'css');
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('page/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Page</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 50px;">Image</th>
                <th style="text-align: left; width: 300px">Title</th>
                <th style="text-align: left;">Page type</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $navigation Model_Page_Page */ ?>
            <?php foreach ($params['collection'] as $page) : ?>
                <tr>
                    <td style="text-align: center"><div class="page-image-preview" style="background-image: url(<?= $this->imageOptimize($page->getImageUrl(), 300, 300, false) ?>)"></div></td>
                    <td style="text-align: left"><?= $page->data['title'] ?></td>
                    <td style="text-align: left"><?= $page->getPageType() ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?= $page->getDetailUrl() ?>" target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                        <?php if ($this->checkPermission('page/edit')) : ?>
                            <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $page->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('page/delete')) : ?>
                            <a class="btn btn-small btn-icon" <?php if ($page->isSystemPage()) : ?>disabled="disabled"<?php else : ?>href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the page \"{$page->data['title']}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $page->getId())) ?>')"<?php endif; ?>><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($page->data['child_page']) :?>
                    <?php foreach ($page->data['child_page'] as $child_page): ?>
                        <tr>
                            <td style="text-align: center"><div class="page-image-preview" style="background-image: url(<?= $this->imageOptimize($child_page->getImageUrl(), 300, 300, false) ?>)"></div></td>
                            <td style="text-align: left"><?= '— ' . $child_page->data['title'] ?></td>
                            <td style="text-align: left"><?= $page->getPageType() ?></td>
                            <td style="text-align: right">
                                <a class="btn btn-small btn-icon" href="<?= $child_page->getDetailUrl() ?>" target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                                <?php if ($this->checkPermission('page/edit')) : ?>
                                    <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $child_page->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                                <?php endif; ?>
                                <?php if ($this->checkPermission('page/delete')) : ?>
                                    <a class="btn btn-small btn-icon" <?php if ($child_page->isSystemPage()) : ?>disabled="disabled"<?php else : ?>href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the page \"{$child_page->data['title']}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $child_page->getId())) ?>')"<?php endif; ?>><?= $this->getIcon('trash-alt-regular') ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif;?>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>