<?php
/* @var $this Helper_Backend_Template */
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create Group</a>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="text-align: left">Name</th>
                <th style="width: 100px; text-align: right">Members</th>
                <th style="width: 100px; text-align: center">Locked</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $group Model_User_Group */ ?>
            <?php foreach ($params['collection'] as $group) : ?>        
                <tr>
                    <td style="text-align: center"><?php echo $group->getId(); ?></td>
                    <td style="text-align: left"><?php echo $group->data['title']; ?></td>
                    <td style="text-align: right"><?php echo $group->countMembers(); ?></td>
                    <td style="text-align: center">
                        <div style="width: 20px;padding: 5px;text-align: center;margin: 0 auto;">
                        <?php if ($group->data['lock_flag']) {
                            echo $this->getIcon('lock-alt-solid');
                        } ?>
                        </div>
                    </td>
                    <td style="text-align: right">
                        <?php if ($group->data['lock_flag'] && !$this->getAccount()->isRoot()) : ?>
                            <a href="javascript: void(0)" disabled="disabled" class="btn btn-small btn-icon"><?= $this->getIcon('pencil') ?></a>
                        <?php else : ?>
                            <a href="<?php echo $this->getUrl('*/*/post', array('id' => $group->getId())); ?>" class="btn btn-small btn-icon"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($group->isRoot() || ($group->data['lock_flag'] && !$this->getAccount()->isRoot())) : ?>
                            <a class="btn btn-small btn-icon" href="javascript: void(0)" disabled="disabled"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php else : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?php echo $this->safeString("Do you want to delete the group \"{$group->data['title']}\"?"); ?>', '<?php echo $this->getUrl('*/*/delete', array('id' => $group->getId())); ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="no-result">No group was found to display</div>
    <?php endif; ?>
</div>