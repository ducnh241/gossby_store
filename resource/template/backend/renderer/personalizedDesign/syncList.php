<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_PersonalizedDesign_Sync_Collection */
/* @var $sync Model_PersonalizedDesign_Sync */
$this->push('core/cron.js', 'js');
$collection = $params['collection'];
$badge_color = [
    'queue' => 'blue',
    'running' => 'green',
    'error' => 'red'
];
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <?php if($this->checkPermission('personalized_design/sync_queue/delete')) :  ?>
                <div class="btn btn-primary btn-small" data-insert-cb="initCoreCronBulkActionBtn"  data-link="<?= $this->getUrl('*/*/delete') ?>" data-confirm="Do you want to delete selected queues?">Delete</div>
            <?php endif ?>
            <?php if($this->checkPermission('personalized_design/sync_queue/requeue')) :  ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initCoreCronBulkActionBtn" data-link="<?= $this->getUrl('*/*/reQueue') ?>">Requeue</div>
            <?php endif ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='queue_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 10px; text-align: left">ID</th>
                <th style="text-align: left; width: 150px">Ukey</th>
                <th style="text-align: left; width: 150px">Type</th>
<!--                <th style="text-align: left">Type</th>-->
                <th style="width: 170px; text-align: left">State</th>
                <th style="width: 160px; text-align: left">Error</th>
                <th style="width: 170px; text-align: left">Added date</th>
                <th style="width: 170px; text-align: left">Modified date</th>
            </tr>
            <?php foreach ($collection as $sync) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="queue_id" value="<?= $sync->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: left">#<?= $sync->getId() ?></td>
                    <td style="text-align: left"><?= $sync->data['ukey'] ?></td>
                    <td style="text-align: left"><?= $sync->data['sync_type'] ?></td>
                    <td style="text-align: left"><span class="badge badge-<?= $badge_color[strtolower($sync->getSyncFlagCode())] ?>"><?= $sync->getSyncFlagCode() ?></span></td>
                    <td style="text-align: left"><?= $sync->data['sync_error'] ?></td>
                    <td style="text-align: left; white-space: nowrap;"><?= date('d/m/Y H:i:s', $sync->data['added_timestamp']) ?></td>
                    <td style="text-align: left; white-space: nowrap;"><?= $sync->data['modified_timestamp'] > 0 ? date('d/m/Y H:i:s', $sync->data['modified_timestamp']) : '-/-' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No personalized design sync was found to display</div>
    <?php endif; ?>
</div>