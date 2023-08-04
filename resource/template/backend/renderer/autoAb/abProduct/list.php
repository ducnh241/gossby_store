<?php
$config_collection = $params['config_collection'];

?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('abProduct/super|abProduct/product/add')) : ?>
                <a href="<?= $this->getUrl('*/*/getPostForm'); ?>" class="btn btn-primary btn-small mr10">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add new Campaign
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($config_collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 50px; text-align: center">ID</th>
                <th style="text-align: left">Title</th>
                <th style="text-align: left">Begin Time</th>
                <th style="text-align: left">Finish Time</th>
                <th style="text-align: left">Status</th>
                <th style="width: 70px; text-align: right"></th>
            </tr>
            <?php foreach ($config_collection as $model) : ?>
                <tr>
                    <td style="width: 50px; text-align: center"><?= $model->getId() ?></td>
                    <td style="text-align: left">
                        <div>
                            <?= $model->data['title'] ?>
                        </div>
                    </td>
                    <td style="text-align: left">
                        <div>
                            <?= $model->data['begin_time'] > 0 ? date('d/m/Y', $model->data['begin_time']) : 0 ?>
                        </div>
                    </td>
                    <td style="text-align: left">
                        <div>
                            <?= $model->data['finish_time'] > 0 ? date('d/m/Y', $model->data['finish_time']) : 0 ?>
                        </div>
                    </td>
                    <td style="text-align: left">
                        <div>
                            <?= $model->getStatusName() ?>
                        </div>
                    </td>
                    <td style="text-align: right; width: 150px">
                        <button
                            class="btn btn-small btn-icon"
                            title="Copy link"
                            onclick="copyToClipboard(['<?= $model->getHubUrl() ?>'])"
                        >
                            <?= $this->getIcon('external-link-regular') ?>
                        </button>

                        <?php if ($this->checkPermission('abProduct/super|abProduct/product/add|abProduct/product/edit')
                        ) : ?>
                            <a class="btn btn-small btn-icon"
                               title="Duplicate"
                               href="<?= $this->getUrl('*/*/duplicate', [
                                   'id' => $model->getId()
                               ]); ?>">
                                <?= $this->getIcon('clone') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('abProduct/super|abProduct/product/view_tracking')) : ?>
                            <a class="btn btn-small btn-icon"
                               title="View tracking"
                               href="<?= $this->getUrl('*/*/viewTracking', ['id' => $model->getId()]); ?>">
                                <?= $this->getIcon('analytics') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('abProduct/super|abProduct/product/edit')
                        ) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?= $this->getUrl('*/*/getPostForm', [
                                   'id' => $model->getId()
                               ]); ?>">
                                <?= $this->getIcon('pencil') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('abProduct/super|abProduct/product/delete')) : ?>

                            <?php if($model->data['status'] == Model_AutoAb_AbProduct_Config::STATUS_CREATED) : ?>
                                <?php $confirm_message = "Do you want to delete the campaign hub \"{$model->data['title']}\"?"?>
                            <?php else : ?>
                                <?php $confirm_message = "Deleting will lose data. Do you want to delete the campaign hub \"{$model->data['title']}\"?"?>
                            <?php endif; ?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString($confirm_message) ?>', '<?= $this->getUrl('*/*/delete', ['id' => $model->getId()]) ?>')">
                                <?= $this->getIcon('trash-alt-regular') ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['config_collection']->getCurrentPage(), $params['config_collection']->collectionLength(), $params['config_collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No AB test product was found to display</div>
    <?php endif; ?>
</div>
