<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('itemBrowser', 'autoCompletePopover');
$this->push(['core/cron.js', 'feed/block.js'], 'js');

$badge = [
    'Running' => 'blue',
    'Wait to run' => 'yellow',
    'Error' => 'red',
    'Success' => 'green'
];

$tab_menu = OSC::helper('feed/common')->getTabMenu('bulk_block');

?>

<div class="tab_menu m25">
    <?php foreach ($tab_menu as $item):?>
        <a href="<?= $item['url'] ?>" class="<?= $item['activated'] == true ? 'active' : '' ?> tab_menu__item"><?= $item['title'] ?></a>
    <?php endforeach; ?>
</div>

<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
            <div class="btn btn-primary btn-small ml5"
                 data-insert-cb="initBlockListBtn"
                 data-bulk-block-url="<?= $this->getUrl('*/*/bulkBlock') ?>"
                 data-process-bulk-block-url="<?= $this->getUrl('*/*/processBulkBlock') ?>"
            >Bulk Block</div>
            <div class="btn btn-danger btn-small ml5" data-insert-cb="initBulkLogDeleteBtn"  data-link="<?= $this->getUrl('*/*/deleteBulkBlock') ?>" data-confirm="Do you want to delete selected queues?">Delete</div>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/searchBulkBlockLog'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">
                    <div class="styled-checkbox">
                        <input type="checkbox" data-insert-cb="initCheckboxSelectAll"
                                                        data-checkbox-selector="input[name='queue_id']"/>
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                </th>
                <th style="width: 10px; text-align: left">ID</th>
                <th style="text-align: center; white-space: nowrap">Category</th>
                <th style="text-align: center; white-space: nowrap">Product SKU</th>
                <th style="text-align: center">Collection ID</th>
                <th style="text-align: center; width: 100px">Country Code</th>
                <th style="text-align: center; width: 100px">Added By</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Message</th>
                <th style="text-align: center; width: 120px">Add time</th>
            </tr>
            <?php /* @var Model_Catalog_Product_BulkQueue $queue */ ?>
            <?php foreach ($params['collection'] as $queue) : ?>
                <?php $queue_data = $queue->data['queue_data'] ?>
                <tr>
                    <td style="text-align: center" class="link--skip">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="queue_id" value="<?= $queue->getId() ?>"/>
                            <ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: left"><?= $queue->getId() ?></td>
                    <td style="text-align: center"><?= $queue_data['block']['category'] ?? '' ?></td>
                    <td style="text-align: center"><?= $queue_data['block']['sku'] ?? '' ?></td>
                    <td style="text-align: center"><?= $queue_data['block']['collection_id'] ?? '' ?></td>
                    <td style="text-align: center"><?= $queue_data['block']['country_code'] ?? '' ?></td>
                    <td style="text-align: center"><?= OSC::model('user/member')->load($queue_data['block']['member_id'])->data['username'] ?? '' ?></td>
                    <td style="text-align: center">
                        <?php if (isset(array_values($badge)[$queue->data['queue_flag']]) && isset(array_keys($badge)[$queue->data['queue_flag']])) : ?>
                            <span class="badge badge-<?= array_values($badge)[$queue->data['queue_flag']] ?>">
                                <?= array_keys($badge)[$queue->data['queue_flag']] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-green">Running</span>
                        <?php endif;?>
                    </td>
                    <td style="text-align: center"><?= $queue->data['error'] ?></td>
                    <td style="text-align: center"><?= date('d/m/Y H:i:s', $queue->data['added_timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No queue was found to display</div>
    <?php endif; ?>
</div>
