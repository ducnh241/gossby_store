<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');

$range = $params['range'];

if (is_array($range)) {
    if ($range[0] == $range[1]) {
        $range = $range[0];
    } else {
        $range = implode('-', $range);
    }
}
?>
<div class="dashboard pb0">
    <div class="filter-menu">
        <ul class="date-range">
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
            <li<?= (is_array($params['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>"
                   data-insert-cb="initReportCustomDate"
                   data-begin="<?= is_array($params['range']) ? $params['range'][0] : '' ?>"
                   data-end="<?= is_array($params['range']) ? $params['range'][1] : '' ?>">Filter</a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<div class="block m25">
    <table class="grid grid-borderless">
        <tr>
            <td style="width: 400px"><b>SMS sent</b></td>
            <td><?= $params['total_sms_sent'] ? $params['total_sms_sent'] : 0?></td>
        </tr>
        <tr>
            <td><b>Customer click</b></td>
            <td><?= $params['total_sms_click'] ? $params['total_sms_click'] : 0 ?></td>
        </tr>
        <tr>
            <td><b>Order placed</b></td>
            <td><?= $params['total_total_order'] ? $params['total_total_order'] : 0 ?></td>
        </tr>
    </table>
</div>