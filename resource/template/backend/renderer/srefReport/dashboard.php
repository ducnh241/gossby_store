<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');

$this->push('vendor/tooltipster/tooltipster.bundle.min.js', 'js');
$this->push(['vendor/tooltipster/tooltipster.bundle.min.css', 'vendor/tooltipster/tooltipster-sideTip-borderless.min.css'], 'css');

$data = $params['meta_data']['data'];

$chart_data = [];
$sum_data = [];

foreach ($data as $key => $records) {
    $chart_data[$key] = [
        'label' => [],
        'value' => []
    ];

    $sum_data[$key] = 0;

    foreach ($records as $record) {
        $chart_data[$key]['label'][] = $record['short_label'];
        $chart_data[$key]['value'][] = $record['value'];

        $sum_data[$key] += $record['value'];
    }
}

$tax = abs($sum_data['tax_price']) - abs($sum_data['refunded_tax_price']);
?>
<div class="dashboard">
    <div class="filter-menu">
        <div class="filter-select">
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
        </div>

        <ul class="date-range" data-insert-cb="initReportFilterByDateCondition">
            <li<?= ($params['meta_data']['range'] == 'today') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'today']); ?>">Today</a></li>
            <li<?= ($params['meta_data']['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'yesterday']); ?>">Yesterday</a></li>
            <li<?= ($params['meta_data']['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thisweek']); ?>">This Week</a></li>
            <li<?= ($params['meta_data']['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastweek']); ?>">Last Week</a></li>
            <li<?= ($params['meta_data']['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'thismonth']); ?>">This Month</a></li>
            <li<?= ($params['meta_data']['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'lastmonth']); ?>">Last Month</a></li>
            <li<?= ($params['meta_data']['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
            <li<?= (is_array($params['meta_data']['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][0] : '' ?>" data-end="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
    </div>

    <div class="loading-overlay-wrapper white-bg">
        <div class="loading-overlay"></div>
    </div>

    <div class="loading-wrapper">
        <div class="dashboard-grid grid-col-4">
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group"><div class="header__heading">Purchases</div></div>
                    </div>
                    <ul class="action-log-widget flex-grid">
                        <li>
                            <div><span class="label">Orders</span>: <strong><?= $sum_data['orders']; ?></strong></div>
                        </li>
                        <li>
                            <div><span class="label">Units Sold</span>: <strong><?= $sum_data['sales']; ?></strong></div>
                        </li>
                        <?php
                        if ($sum_data['vendor_point']) :
                            ?>
                            <li>
                                <div><span class="label">Point</span>:
                                    <strong><?= OSC::helper('catalog/common')->integerToFloat($sum_data['vendor_point']) + OSC::helper('catalog/common')->integerToFloat($sum_data['point']); ?></strong>
                                </div>
                            </li>
                        <?php else:
                            ?>
                            <li>
                                <div><span class="label">Point</span>:
                                    <strong><?= OSC::helper('catalog/common')->integerToFloat($sum_data['point']); ?></strong>
                                </div>
                            </li>
                        <?php
                        endif; ?>
                    </ul>
                </div>
            </div>
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group"><div class="header__heading">Profit</div></div>
                    </div>
                    <ul class="action-log-widget flex-grid">
                        <li>
                            <div><span class="label tooltip" title="The total income received, excluding cancelled orders">Revenue</span>: <strong><?= OSC::helper('catalog/common')->formatPrice($sum_data['revenue']); ?></strong></div>
                        </li>
                        <li>
                            <div><span class='label tooltip'
                                       title='The total amount of tips received, excluding cancelled orders'>Tip</span>:
                                <strong><?= OSC::helper('catalog/common')->formatPrice(isset($sum_data['tip_price']) ? $sum_data['tip_price'] : 0); ?></strong>
                            </div>
                        </li>
                        <li>
                            <div><span class="label tooltip" title="The total amount issued to customers from all refunded orders">Refunded</span>: <strong><?= OSC::helper('catalog/common')->formatPrice(abs($sum_data['refunded_price'])); ?></strong></div>
                        </li>
                        <li>
                            <div><span class='label tooltip'
                                       title='Tax: Amount Tax - Amount tax refunded'>Tax</span>:
                                <strong><?= OSC::helper('catalog/common')->formatPrice(abs($tax)); ?></strong>
                            </div>
                        </li>
                        <li>
                            <div><span class="label tooltip" title="Net Revenue: Revenue - Refunded - Tax">Net Revenue</span>: <strong><?= OSC::helper('catalog/common')->formatPrice($sum_data['revenue'] - abs($sum_data['refunded_price'] - abs($tax))); ?></strong></div>
                        </li>
                        <li></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="dashboard-grid grid-col-2">
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Order/Sale</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <canvas id="canvas_chart_order"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="block">
                    <div class="header">
                        <div class="header__main-group">
                            <div class="header__heading">Revenue</div>
                        </div>
                    </div>
                    <div>
                        <div class="report-block">
                            <canvas id="canvas_chart_revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.chartColors = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        blue_fill: 'rgba(54, 162, 235, 0.5)',
        purple: 'rgb(153, 102, 255)',
        purple_fill: 'rgba(153, 102, 255, 0.5)',
        blue_fill_without_opacity: 'rgba(154, 208, 245, 1)',
        purple_fill_without_color: 'rgba(204, 178, 255, 1)',
        grey: 'rgb(201, 203, 207)'
    };
    var config_order = {
        type: 'bar',
        data: {
            labels: ["<?= implode('","', $chart_data['orders']['label']) ?>"],
            datasets: [{
                label: 'Orders',
                backgroundColor: window.chartColors.blue_fill_without_opacity,
                borderColor: window.chartColors.blue_fill_without_opacity,
                borderWidth: 0,
                fill: true,
                data: ["<?= implode('","', $chart_data['orders']['value']) ?>"]
            }, {
                label: 'Sales',
                backgroundColor: window.chartColors.purple_fill_without_color,
                borderColor: window.chartColors.purple_fill_without_color,
                borderWidth: 0,
                fill: true,
                data: ["<?= implode('","', $chart_data['sales']['value']) ?>"]
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Order/Sales <?= $params['meta_data']["type"]; ?>'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (item) {
                        return $.digitGroupping(item.yLabel);
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Time'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Value'
                    },
                    ticks: {
                        beginAtZero: true,
                        userCallback: function (item) {
                            return $.digitGroupping(item);
                        }
                    }
                }]
            }
        }
    };
    var config_revenue = {
        type: 'bar',
        data: {
            labels: ["<?= implode('","', $chart_data['revenue']['label']) ?>"],
            datasets: [{
                label: 'Revenue',
                backgroundColor: window.chartColors.blue_fill,
                borderColor: window.chartColors.blue,
                borderWidth: 0,
                fill: true,
                data: ["<?= implode('","', $chart_data['revenue']['value']) ?>"]
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Revenue'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function (item) {
                        return $.digitGroupping(item.yLabel);
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Time'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Value'
                    },
                    ticks: {
                        beginAtZero: true,
                        userCallback: function (item) {
                            return $.digitGroupping(item);
                        }
                    }
                }]
            }
        }
    };
    window.onload = function () {
        var chart_order_sale = document.getElementById('canvas_chart_order').getContext('2d');
        window.myLine = new Chart(chart_order_sale, config_order);
        var chart_revenue = document.getElementById('canvas_chart_revenue').getContext('2d');
        window.myLine = new Chart(chart_revenue, config_revenue);
    };

    $(document).ready(function () {
        $('.tooltip')?.tooltipster({
            theme: 'tooltipster-borderless'
        });
    });
</script>
