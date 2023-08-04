<?php
/* @var $this Helper_Backend_Template */
$this->push('core/cron.js', 'js');
$error_flag_timestamp = time() - (60 * 60 * 12);
$badge_color = [
    'queue' => 'blue',
    'running' => 'green',
    'warning' => 'yellow',
    'error' => 'red'
];
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <?php if (($this->checkPermission('developer/cron_manager/delete') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) { ?>
                <div class="btn btn-primary btn-small"
                     data-insert-cb="initCoreCronBulkActionBtn"
                     data-link="<?= $this->getUrl('*/*/delete') ?>"
                     data-confirm="<?= $this->safeString('Do you want to delete selected queues?') ?>"
                >Delete</div>
            <?php } ?>
            <?php if (($this->checkPermission('developer/cron_manager/requeue') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) { ?>
                <div class="btn btn-primary btn-small ml5"
                     data-insert-cb="initCoreCronBulkActionBtn"
                     data-link="<?= $this->getUrl('*/*/recron') ?>"
                >Recron</div>
            <?php } ?>
        </div>
    </div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='queue_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 10px; text-align: left">ID</th>
                <th style="text-align: left">Cron name</th>
                <th style="text-align: center; width: 70px">&nbsp;</th>
                <th style="text-align: center; width: 70px">Requeued</th>
                <th style="text-align: left; width: 130px">Begin time</th>
                <th style="text-align: left; width: 130px">Running time</th>
                <th style="text-align: right; width: 150px">&nbsp;</th>
            </tr>
            <?php foreach ($params['collection'] as $cron) : ?>
                <?php
                $badge = 'queue';

                if ($cron->data['error_flag'] == 1) {
                    $badge = 'error';
                } else if ($cron->data['locked_timestamp'] > 0) {
                    if ($cron->data['locked_timestamp'] < $error_flag_timestamp) {
                        $badge = 'warning';
                    } else {
                        $badge = 'running';
                    }
                }
                ?>
                <tr data-insert-cb="initClickAndGo" data-execute="initCoreCronGetInfo" data-link="<?= $this->getUrl('core/backend_cron/info', ['id' => $cron->getId()]) ?>" data-title="<?= $this->safeString($cron->data['cron_name'] . ' #' . $cron->getId()) ?>">
                    <td style="text-align: center" class="link--skip"><div class="styled-checkbox"><input type="checkbox" name="queue_id" value="<?= $cron->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins></div></td>
                    <td style="text-align: left"><?= $cron->getId() ?></td>
                    <td style="text-align: left"><?= $cron->data['cron_name'] ?></td>
                    <td style="text-align: center"><span class="badge badge-<?= $badge_color[$badge] ?>"><?= $badge ?></span></td>
                    <td style="text-align: center"><?= $cron->data['requeue_counter'] ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $cron->data['running_timestamp']) ?></td>
                    <td style="text-align: left"><?= $cron->data['locked_timestamp'] > 0 ? date('d/m/Y H:i', $cron->data['locked_timestamp']) : '-/-' ?></td>
                    <td style="text-align: right">
                        <?php if (($this->checkPermission('developer/cron_manager/requeue') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) { ?>
                            <?php if ($cron->data['locked_timestamp'] > 0) : ?>
                                <?php if ($cron->data['error_flag'] == 1 || $cron->data['locked_timestamp'] < $error_flag_timestamp): ?>
                                    <a class="btn btn-small btn-icon link--skip" href="<?= $this->getUrl('*/*/recron', ['id' => $cron->getId()]) ?>"><?= $this->getIcon('redo') ?></a>
                                <?php endif; ?>
                            <?php else : ?>
                                <a class="btn btn-small btn-icon link--skip" href="<?= $this->getUrl('*/*/execute', ['id' => $cron->getId()]) ?>"><?= $this->getIcon('play-solid') ?></a>
                            <?php endif; ?>
                        <?php } ?>
                        <span class="btn btn-small btn-icon"><?= $this->getIcon('eye-regular') ?></span>
                        <?php if (($this->checkPermission('developer/cron_manager/delete') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) { ?>
                            <a class="btn btn-small btn-icon link--skip" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the queue {$cron->data['cron_name']}?") ?>', '<?= $this->getUrl('*/*/delete', ['id' => $cron->getId()]) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No queue was found to display</div>            
    <?php endif; ?>
</div>