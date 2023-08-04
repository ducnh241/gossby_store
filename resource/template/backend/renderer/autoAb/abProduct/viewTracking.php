<?php
/* @var $this Helper_Backend_Template */
$this->push('core/setting.scss', 'css');
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss', 'abProduct/common.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js', 'autoAb/abProduct.js'), 'js');

$data = $params['data'];

$config = $params['config']

?>
<div class="dashboard">
    <div class="filter-menu">
        <ul class="date-range" id="range" data-id = '<?= $config->getId() ?>'>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='today'>Today</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='yesterday'>Yesterday</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='thisweek'>This Week</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='lastweek'>Last Week</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='thismonth'>This Month</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='lastmonth'>Last Month</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initDateRange" data-range='alltime'>All Time</a></li>
            <li class="range-item"><a href="javascript://" data-insert-cb="initCustomDateRange" data-range='custom'>Custom</a></li>
            <?= $this->getJSONTag($params['product_titles'], 'product-titles') ?>
        </ul>
    </div>
</div>

<div class="title pl25 bold"><?= $config->data['title'] . ' (' .date('d/m/Y', $config->data['begin_time']) . ' - ' . date('d/m/Y', $config->data['finish_time']) . ')'?></div>

<div class="setting-config-panel post-frm m0 p25 distribution-table d-none">
    <div class="setting-config-group">
        <div class="frm-grid frm-grid--separate">
            <div class="setting-item">
                <div class="mb15">Distribution</div>
                <div class="setting-table">
                    <table>
                        <tbody>
                        <tr>
                            <th>Product ID</th>
                            <th>Name</th>
                            <th>Acquisition</th>
                        </tr>
                        <?php foreach ($params['product_maps'] as $product_map) : ?>
                            <?php if (isset($params['product_titles'][$product_map['product_id']])) : ?>
                                <tr>
                                    <td><?= $product_map['product_id'] ?></td>
                                    <td style="text-align: left;width: 350px"><?= $params['product_titles'][$product_map['product_id']] ?></td>
                                    <td><?= $product_map['acquisition'] ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="setting-config-panel post-frm m0 p25 result-table">
    <div class="setting-config-group">
        <div class="frm-grid frm-grid--separate">
            <div class="setting-item">
                <div class="mb15">A/B Test result</div>
                <div class="setting-table">
                    <table></table>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            Go Back
        </a>
    </div>
</div>
