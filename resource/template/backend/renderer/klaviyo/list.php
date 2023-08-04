<?php
/* @var $this Helper_Backend_Template */
$this->push('core/cron.js', 'js');
$this->push('klaviyo/common.js', 'js');
$error_flag_timestamp = time() - (60 * 60 * 12);
$badge_color = [
    'queue' => 'blue',
    'running' => 'green',
    'error' => 'red'
];
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <?php if ($this->checkPermission('klaviyo/delete')) : ?>
                <div class="btn btn-primary btn-small ml5" data-insert-cb="initKlaviyoBulkDeleteBtn" data-link="<?= $this->getUrl('*/*/delete') ?>">Delete</div>
            <?php endif; ?>
            <?php if ($this->checkPermission('klaviyo/requeue')) : ?>
            <div class="btn btn-primary btn-small ml5" data-insert-cb="initKlaviyoBulkRequeueBtn" data-link="<?= $this->getUrl('*/*/requeue') ?>">Requeue</div>
            <?php endif; ?>
            <?php if ($this->checkPermission('klaviyo/recron')) : ?>
                <a class="btn btn-primary btn-small ml5" href="<?= $this->getUrl('*/*/recron') ?>">Recron</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='record_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="width: 10px; text-align: left">ID</th>
                <th style="text-align: center; width: 70px">Status</th>
                <th style="text-align: center; width: 400px">Data</th>
                <th style="text-align: center;">Message</th>
                <th style="text-align: left; width: 120px">Add time</th>
            </tr>
            <?php foreach ($params['collection'] as $queue) : ?>
                <?php
                $badge = 'queue';

                if ($queue->data['queue_flag'] == Model_Klaviyo_Item::FLAG_QUEUE_RUNNING) {
                    $badge = 'running';
                } else if ($queue->data['queue_flag'] == Model_Klaviyo_Item::FLAG_QUEUE_ERROR) {
                    $badge = 'error';
                }
                unset($queue->data['data']['token']);
                unset($queue->data['data']['event']);
                ?>
                <tr>
                    <td style="text-align: center" class="link--skip"><div class="styled-checkbox"><input type="checkbox" name="record_id" value="<?= $queue->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins></div></td>
                    <td style="text-align: left"><?= $queue->getId() ?></td>
                    <td style="text-align: center"><span class="badge badge-<?= $badge_color[$badge] ?>"><?= $badge ?></span></td>
                    <td style="text-align: center; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;max-width: 1000px;"><?= OSC::encode($queue->data['data']) ?></td>
                    <td style="text-align: center"><?= $queue->data['error_message'] ?></td>
                    <td style="text-align: left;"><?= date('d/m/Y H:i', $queue->data['added_timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No queue was found to display</div>            
    <?php endif; ?>
</div>