<?php
/* @var $this Helper_Backend_Template */
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('navigation/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Navigation</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="text-align: left">Title</th>
                <th style="width: 70px; text-align: right"></th>
            </tr>
            <?php /* @var $navigation Model_Navigation_Navigation */ ?>
            <?php foreach ($params['collection'] as $navigation) : ?>
                <tr>
                    <td style="text-align: center"><?= $navigation->getId() ?></td>
                    <td style="text-align: left"><?= $navigation->data['title'] ?></td>
                    <td style="text-align: right">            
                        <?php if ($this->checkPermission('navigation/edit')) : ?>            
                            <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $navigation->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('navigation/delete')) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the navigation \"{$navigation->data['title']}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $navigation->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No navigations added yet.</div>
    <?php endif; ?>
</div>