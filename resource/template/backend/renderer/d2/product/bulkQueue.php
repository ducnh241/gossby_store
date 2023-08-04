<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_Catalog_Product_Collection */
$this->push(['core/cron.js', 'd2/product.js'], 'js');

$badge_color = [
    'running' => 'blue',
    'wait to run' => 'yellow',
    'error' => 'red'
];

?>

<div class="block mt25">
    <div class="header-grid">
        <div class="flex--grow">
            <div class="btn btn-primary btn-small ml5" data-insert-cb="initReCronActionBtn"
                 data-process-url="<?= $this->getUrl('*/*/recronProcessQueue') ?>">Recron
            </div>

            <div class="btn btn-danger btn-small ml5" data-insert-cb="initDeleteCronActionBtn"  data-process-url="<?= $this->getUrl('*/*/deleteCronProcessQueue') ?>" data-confirm="Do you want to delete selected queues?">
                Delete</div>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/searchListBulkQueue'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless" data-insert-cb="tooltipInit">
            <tr>
                <th style="width: 10px; text-align: center">
                    <div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll"
                                                        data-checkbox-selector="input[name='queue_id']"/>
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                </th>
                <th style="width: 10px; text-align: left">ID</th>
                <th style="text-align: center; white-space: nowrap">action</th>
                <th style="text-align: center; ">Status</th>
                <th style="text-align: center;">Message</th>
                <th style="text-align: left; width: 120px">Add time</th>
                <th style="text-align: left;"></th>
            </tr>
            <?php foreach ($params['collection'] as $queue) : ?>

                <?php
                switch ($queue->data['action']){
                    case 'renderDesignSvgBeta': {
                        $order_line = "[{$queue->data['queue_data']['line_item_id']}] ";
                        break;
                    }
                    case 'retry_d2FlowReply':
                    case 'd2FlowReply': {
                        $order_line = "[{$queue->data['queue_data']['orderItemId']}] ";
                        break;
                    }
                    case 'update_raw_airtable': {
                        $order_line = '[' . (explode('_', $queue->data['ukey'])[1] ?? '') . '] ';
                        break;
                    }
                    default: {
                        $order_line = '';
                    }
                }
                ?>

                <tr>
                    <td style="text-align: center" class="link--skip">
                        <div class="styled-checkbox"><input type="checkbox" name="queue_id"
                                                            value="<?= $queue->getId() ?>"/>
                            <ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: left"><?= $queue->getId() ?></td>
                    <td style="text-align: center"><?= $queue->data['action'] ?></td>
                    <td style="text-align: center">
                        <span class="badge badge-<?= array_values($badge_color)[$queue->data['queue_flag']] ?>">
                            <?= ucfirst(array_keys($badge_color)[$queue->data['queue_flag']]) ?>
                        </span>
                    </td>
                    <td style="text-align: center"><?= $queue->data['error'] ? ($order_line . $queue->data['error']) : '' ?></td>
                    <td style="text-align: left;"><?= date('d/m/Y H:i', $queue->data['added_timestamp']) ?></td>
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
