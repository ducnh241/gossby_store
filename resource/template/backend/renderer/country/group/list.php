<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('select2');
$this->addComponent('location_group');
?>

<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <a href="javascript:void(0)" class="btn btn-primary btn-small" data-insert-cb="initAddNewGroupLocation" data-key="<?= OSC::makeUniqid() ?>"><?= $this->getIcon('plus', array('class' => 'mr5')) ?> New Location Group</a>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="width: 10px; text-align: left">Group Name</th>
                <th style="width: 200px; text-align: left">Added time</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['collection'] as $model) : ?>

                <tr>
                    <td style="text-align: center"><?php echo $model->getId(); ?></td>
                    <td style="text-align: left"><?php echo $model->data['group_name']; ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $model->data['added_timestamp']) ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="#" data-insert-cb="initAddNewGroupLocation" data-value-include="<?= $model->data['group_data']['include'] ?>" data-value-exclude="<?= $model->data['group_data']['exclude'] ?>" data-value-group-name="<?= $model->data['group_name'] ?>" data-value-id = "<?= $model->getId(); ?>" ><?= $this->getIcon('pencil') ?></a>
                        <?php if ($this->getAccount()->isAdmin()) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?php echo htmlentities("Do you want to delete the group name: \"{$model->data['group_name']}\"", ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>', '<?php echo $this->getUrl('*/*/delete', array('id' => $model->getId())); ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table> 
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">Not found country group</div>
    <?php endif; ?>
</div>