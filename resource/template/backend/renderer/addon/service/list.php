<?php
/* @var $this Helper_Backend_Template */

$this->push(['addon/service.js'], 'js')
    ->push(['addon/service.scss'], 'css');

$addon_services = $params['addon_services'];
?>
<div class="block m25">
    <div class="header">
        <div class="flex--grow">
            <?php if ($this->checkPermission('addon_service/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create New Add-on</a>
            <?php endif; ?>
        </div>

        <div class="header__main-group">
            <div class="header__heading">&nbsp</div>
        </div>
        <div class="header__action-group"></div>
    </div>
    <?php if ($addon_services->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">
                    <div class="styled-checkbox">
                        <input type="checkbox" data-insert-cb="initCheckboxSelectAll"
                               data-checkbox-selector="input[name='queue_id']"/>
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                </th>
                <th style="width: 100px; text-align: center">ID</th>
                <th style="text-align: left">Campaign Title</th>
                <th style="text-align: left; width: 300px">Add-on Type</th>
                <th style="text-align: left; width: 300px">Add-on Status</th>
                <th style="text-align: left; width: 300px">A/B Test Status</th>
                <th style="text-align: left; width: 200px">Date Added</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $addon_service Model_Addon_Service */ ?>
            <?php foreach ($addon_services as $addon_service) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="queue_id" value="<?= $addon_service->getId() ?>"/>
                            <ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center"><?= $addon_service->getId() ?></td>
                    <td style="text-align: left; word-break: break-word;" width="30%"
                        id="title-<?= $addon_service->getId() ?>"><?= $addon_service->data['title'] ?></td>
                    <td style="text-align: left"><?= $addon_service->getTypeName(); ?></td>
                    <td style="text-align: left">
                        <?php if ($addon_service->data['status']): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-red">Deactive</span>
                        <?php endif ?>
                    </td>
                    <td style="text-align: left">
                        <?php if ($addon_service->isRunningAbTest()): ?>
                            <span class="badge badge-green">Running</span>
                        <?php else: ?>
                            --
                        <?php endif ?>
                    </td>
                    <td style="text-align: left">
                        <?= date("d/m/Y h:i:s", $addon_service->data['added_timestamp']) ?>
                    </td>
                    <td style="text-align: right">
                        <div style="display: flex">
                            <?php if ($this->checkPermission('addon_service/report')) : ?>
                                <a class="btn btn-small btn-icon"
                                   href="<?= $this->getUrl('*/backend_report/detail', ['id' => $addon_service->getId()]) ?>">
                                    <?= $this->getIcon('report') ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($this->checkPermission('addon_service/view')) : ?>
                                <a class="btn btn-small btn-icon"
                                   href="<?= $this->getUrl('*/*/post', array('mode' => 'view', 'id' => $addon_service->getId())); ?>"><?= $this->getIcon('eye-regular') ?></a>
                            <?php endif; ?>
                            <?php if ($this->checkPermission('addon_service/edit')) : ?>
                                <a class="btn btn-small btn-icon"
                                   href="<?= $this->getUrl('*/*/post', array('id' => $addon_service->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                            <?php endif; ?>
                            <?php if ($this->checkPermission('addon_service/delete')) : ?>
                                <?php $addon_service_title = addslashes($addon_service->data['title']); ?>
                                <a class="btn btn-small btn-icon"
                                   href="javascript:$.confirmAction('<?= $this->safeString(addslashes("Do you want to delete the add-on service \"{$addon_service_title}\"?")) ?>', '<?= $this->getUrl('*/*/delete', array('id' => $addon_service->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($addon_services->getCurrentPage(), $addon_services->collectionLength(), $addon_services->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No Add-on service created yet.</div>
    <?php endif; ?>
</div>
