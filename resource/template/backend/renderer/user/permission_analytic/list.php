<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_User_PermissionAnalytic_Collection */
$collection = $params['collection'];
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('catalog/super|report|srefReport')) : ?>
                <a href="<?= $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Set Permissions</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 20px; text-align: left">Member</th>
                <th style="width: 20px; text-align: left">Viewing Permissions</th>
                <th style="width: 70px; text-align: right"></th>
            </tr>
            <?php foreach ($collection as $permAnalytic) : ?>
                <tr>
                    <td style="text-align: center"><?= $permAnalytic->getId() ?></td>
                    <td style="text-align: left"><?= $permAnalytic->getNameOfMember() ?></td>
                    <td style="text-align: left"><?= $permAnalytic->getNamesOfAnalyticMember() ?></td>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('catalog/super|catalog/super|report|srefReport')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?= $this->getUrl('*/*/post', array('id' => $permAnalytic->getId())); ?>"
                            >
                                <?= $this->getIcon('pencil') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('catalog/super|catalog/super|report|srefReport')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString("Are you sure you want to remove viewing permission from \"{$permAnalytic->getNameOfMember()}\"?") ?>', '<?= $this->getUrl('*/*/delete', ['id' => $permAnalytic->getId()]) ?>')"
                            >
                                <?= $this->getIcon('trash-alt-regular') ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?= $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No Viewing Permissions set yet.</div>
    <?php endif; ?>
</div>
