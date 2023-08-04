<?php
/* @var $this Helper_Backend_Template */
$this->push('postOffice/emailQueue.js', 'js');
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <?php if (($this->checkPermission('developer/master_sync/delete') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) { ?>
                <div class="btn btn-primary btn-small"
                     data-insert-cb="initPostOfficeEmailQueueBulkActionBtn"
                     data-link="<?= $this->getUrl('*/*/delete') ?>"
                     data-confirm="<?= $this->safeString('Do you want to delete selected queues?') ?>"
                >Delete</div>
            <?php } ?>
            <?php if (($this->checkPermission('developer/master_sync/requeue') && OSC::isPrimaryStore()) || (OSC::cookieGet(OSC_IS_DEVELOPER_KEY) == 1 && !OSC::isPrimaryStore())) { ?>
                <div class="btn btn-primary btn-small ml5"
                     data-insert-cb="initPostOfficeEmailQueueBulkActionBtn"
                     data-link="<?= $this->getUrl('*/*/requeue') ?>"
                >Requeue</div>
            <?php } ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='queue_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="text-align: left; width: 50px">ID</th>
                <th style="text-align: left; width: 200px">Sync key</th>
                <th style="text-align: left; width: 200px">Sync data</th>
                <th style="text-align: left; width: 200px">Sync flag</th>
                <th style="text-align: left; width: 130px">Running time</th>
                <th style="text-align: left; width: 130px">Added time</th>
                <th style="text-align: left; width: 130px">Modified time</th>
            </tr>
            <?php foreach ($params['collection'] as $cron) : ?>
                <tr data-insert-cb="initClickAndGo" data-execute="initPostOfficeEmailQueueGetInfo" data-link="<?= $this->getUrl('*/*/info', ['id' => $cron->getId()]) ?>" data-title="<?= $this->safeString($cron->data['cron_name'] . ' #' . $cron->getId()) ?>">
                    <td style="text-align: center" class="link--skip"><div class="styled-checkbox"><input type="checkbox" name="queue_id" value="<?= $cron->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins></div></td>
                    <td style="text-align: left"><?= $cron->data['queue_id'] ?></td>
                    <td style="text-align: left"><?= $cron->data['sync_key'] ?></td>
                    <td style="text-align: left"><?= $cron->data['sync_data'] ?></td>
                    <td style="text-align: left"><?= intval($cron->data['sync_flag']) ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $cron->data['running_timestamp']) ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $cron->data['added_timestamp']) ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i', $cron->data['modified_timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No queue was found to display</div>
    <?php endif; ?>
</div>