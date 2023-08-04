<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');
$data = $params['meta_data']['data'];

$range = $params['meta_data']['range'];

if (is_array($range)) {
    if ($range[0] == $range[1]) {
        $range = $range[0];
    } else {
        $range = implode('-', $range);
    }
}
?>
<div class="dashboard">
    <div class="filter-menu">
        <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
            <?= $this->build('srefReport/memberSelect', ['action' => $params['action'], 'selectors' => $params['selectors']])?>
        <?php endif; ?>

        <?php if (OSC::controller()->checkPermission('report', false) && $params['is_sref_report'] == 0 && count($params['ab_test_keys']['keys']) > 0) : ?>
            <div class="ab-test">
                <div>
                    <div class="styled-select styled-select--small">
                        <?= $this->getJSONTag($params['ab_test_keys'], 'report_ab_test') ?>
                        <select data-link="<?= $this->rebuildUrl(['ab_test_key' => '', 'ab_test_value' => '']); ?>" data-insert-cb="initReportFilterABTest">
                            <option value="">All Results</option>
                            <?php foreach ($params['ab_test_keys']['keys'] as $key => $key_options) : ?>
                                <option value="<?= $this->safeString($key) ?>"<?php if (is_array($params['ab_test_keys']['current']) && $key == $params['ab_test_keys']['current']['key']) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($key) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <ins></ins>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <ul class="date-range" data-insert-cb="initReportFilterByDateCondition">
            <li<?= ($params['meta_data']['range'] == 'today') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'today', 'page' => 1]); ?>">Today</a></li>
            <li<?= ($params['meta_data']['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'yesterday', 'page' => 1]); ?>">Yesterday</a></li>
            <li<?= ($params['meta_data']['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thisweek', 'page' => 1]); ?>">This Week</a></li>
            <li<?= ($params['meta_data']['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastweek', 'page' => 1]); ?>">Last Week</a></li>
            <li<?= ($params['meta_data']['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thismonth', 'page' => 1]); ?>">This Month</a></li>
            <li<?= ($params['meta_data']['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastmonth', 'page' => 1]); ?>">Last Month</a></li>
            <li<?= ($params['meta_data']['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'alltime', 'page' => 1]); ?>">All time</a></li>
            <li<?= (is_array($params['meta_data']['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '', 'page' => 1]); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][0] : '' ?>" data-end="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<div class="block m25">
    <div class="loading-overlay-wrapper">
        <div class="loading-overlay"></div>
    </div>

    <div class="loading-wrapper">
        <?php if (count($data) > 0) : ?>
            <table class="grid grid-borderless">
                <tr>
                    <th style="text-align: left">Product</th>
                    <th style="width: 170px; text-align: left">Orders</th>
                    <th style="width: 150px; text-align: left">Sales</th>
                    <th style="text-align: left">Profit</th>
                    <th style="width: 135px; text-align: right"></th>
                </tr>
                <?php
                    foreach ($data as $product_id => $item):
                ?>
                    <tr>
                        <td style="text-align: left"><a href="<?= $item['url'] ?>"><?= ($item['topic'] ? $item['topic'] . ' - ' : '') . $item['title'] ?></a></td>
                        <td style="text-align: left"><?= number_format($data[$product_id]['orders']) ?></td>
                        <td style="text-align: left"><?= number_format($data[$product_id]['sales']) ?></td>
                        <td style="text-align: left"><?php echo OSC::helper('catalog/common')->formatPrice($data[$product_id]['revenue']) ?></td>
                        <td style="text-align: right">
                            <a class="btn btn-small btn-icon" title="View detail analytics" href="<?php echo $this->getUrl('*/*/productDetail', ['id' => $product_id, 'range' => $range, 'sref_member_id' => $params['sref_member_id'], 'sref_group_id' => $params['sref_group_id']]); ?>"><?= $this->getIcon('eye-regular') ?></a>
                        </td>
                    </tr>
                <?php
                    endforeach;
                ?>
            </table>
            <?php $pager = $this->buildPager($params['meta_data']['current_page'], $params['meta_data']['total_rows'], $params['meta_data']['page_size'], 'page'); ?>
            <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
        <?php else : ?>
            <div class="no-result">No data to display</div>
        <?php endif; ?>
    </div>
</div>
