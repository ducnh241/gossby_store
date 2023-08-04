<?php
/* @var $this Helper_Backend_Template */
$this->push('core/cron.js', 'js');
$collection = $params['collection'];

$badge_color = [
    'running' => 'green',
    'waiting to run' => 'yellow',
    'error' => 'red',
    'success' => 'blue'
];
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <?php if ($this->checkPermission('personalized_design/rerender/delete_log')) : ?>
                <div class="btn btn-primary btn-small" data-insert-cb="initCoreCronBulkActionBtn"  data-link="<?= $this->getUrl('*/*/delete') ?>" data-confirm="Do you want to delete selected queues?">Delete</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='queue_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="text-align: center">Design ID</th>
                <th style="text-align: center">Order ID</th>
                <th style="text-align: center">Line item ID</th>
                <th style="width: 100px; text-align: center">State</th>
                <th style="text-align: center">Error</th>
                <th style="width: 100px; text-align: center">Rerender By</th>
                <th style="width: 100px; text-align: center">Added Date</th>
            </tr>
            <?php foreach ($collection as $log) : ?>

                <?php
                    try {
                        /* @var $member Model_User_Member */
                        $member = OSC::model('user/member')->load($log->data['member_id']);
                    } catch (Exception $ex) {}
                ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="queue_id" value="<?= $log->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center">#<?= $log->getId() ?></td>
                    <td style="text-align: center"><?= $log->data['design_id'] ?></td>
                    <td style="text-align: center"><?= $log->data['order_id'] ?></td>
                    <td style="text-align: center"><?= $log->data['order_item_id'] ?></td>
                    <td style="text-align: center">
                        <span class="badge badge-<?= array_values($badge_color)[$log->data['status']] ?? 'green' ?>">
                            <?= ucfirst(array_keys($badge_color)[$log->data['status']] ?? 'running') ?>
                        </span>
                    </td>
                    <td style="text-align: left"><?= $log->data['message'] ?></td>
                    <td style="text-align: center"><?= $member->data['username'] ?? 'No name' ?></td>
                    <td style="text-align: center"><?= date('d/m/Y H:i:s', $log->data['added_timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?= $pager ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No rerender log was found to display</div>
    <?php endif; ?>
</div>