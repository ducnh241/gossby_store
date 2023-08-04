<?php
/* @var $this Helper_Backend_Template */
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Make Group Administrator</a>
        </div>
    </div>
    <?php if (count($params['admin_groups']) > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 10px; text-align: left">Username</th>
                <th style="width: 200px; text-align: left">Email</th>
                <th style="text-align: left">Group</th>
                <th style="text-align: left">Group Administrators</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['admin_groups'] as $admin_group) : ?>
                <tr>
                    <td style="text-align: center"><?php echo $admin_group['member']->getId(); ?></td>
                    <td style="text-align: left"><?php echo $admin_group['member']->data['username']; ?></td>
                    <td style="text-align: left"><?php echo $admin_group['member']->data['email']; ?></td>
                    <td style="text-align: left"><?php echo $admin_group['member']->getGroup()->data['title']; ?></td>
                    <td style="text-align: left"><?php echo $admin_group['list_group']; ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $admin_group['id'])); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php if ($this->getAccount()->isAdmin()) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?php echo htmlentities("Would you like to remove \"{$admin_group['member']->data['username']}\" as a group administrator?", ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>', '<?php echo $this->getUrl('*/*/delete', array('id' => $admin_group['id'])); ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php else : ?>
                            <a class="btn btn-small btn-icon" href="javascript: void(0)" disabled="disabled"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['current_page'], $params['total'], $params['page_size'], 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No admin privileges were found</div>
    <?php endif; ?>
</div>